<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Section;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\FileType;
use App\Models\Metadata;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\PermissionRegistryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GovernanceHubController extends Controller
{
    /**
     * Display the Unified Governance & Structure Hub
     */
    public function index(Request $request)
    {
        // 1. Recursive Department & Branch Tree
        $departments = Department::with([
            'permissions:id,name',
            'documentType:id,name',
            'fileType:id,name',
            'user:id,department_id,first_name,last_name,email,profile_image,designation_id,is_department_head',
            'designations' => function ($q) {
                $q->where('is_active', true)->orderBy('grade_level', 'desc');
            },
            'division' => function ($query) {
                $query->with([
                    'documentType:id,name',
                    'fileType:id,name',
                    'users:id,division_id,first_name,last_name,email,profile_image,designation_id,is_division_head',
                    'designations' => function ($dq) {
                        $dq->where('is_active', true)->orderBy('grade_level', 'desc');
                    },
                    'unit' => function ($q) {
                        $q->with([
                            'documentType:id,name',
                            'fileType:id,name',
                            'users:id,unit_id,first_name,last_name,email,profile_image,designation_id,is_unit_head',
                            'designations' => function ($uq) {
                                $uq->where('is_active', true)->orderBy('grade_level', 'desc');
                            },
                            'sections' => function ($sq) {
                                $sq->with([
                                    'documentType:id,name',
                                    'fileType:id,name',
                                    'users:id,section_id,first_name,last_name,email,profile_image,designation_id'
                                ])->withCount(['archive', 'users']);
                            }
                        ])->withCount(['archive', 'users', 'sections', 'designations']);
                    }
                ])->withCount(['archive', 'users', 'unit', 'designations']);
            }
        ])->withCount(['archive', 'user', 'division', 'designations'])->get();

        // 2. All Active Designations with Hierarchy Details
        $designations = Designation::with(['department:id,title', 'division:id,title', 'unit:id,title'])
            ->withCount('users')
            ->orderBy('is_active', 'desc')
            ->orderBy('grade_level', 'desc')
            ->get();

        // 3. Spatie Roles with Permissions & Assigned User Counts
        $roles = Role::with('permissions:id,name')->withCount('users')->get();

        // 4. Permissions Grouped by Module via Master Catalog Service
        $groupedPermissions = PermissionRegistryService::getGroupedPermissions();
        $masterCatalog = PermissionRegistryService::getMasterCatalog();

        // 5. Document & File Taxonomy & Custom Metadata Fields
        $documentTypes = DocumentType::with([
            'metadata',
            'departments:id,title',
            'divisions:id,title',
            'units:id,title',
            'sections:id,title'
        ])->get();

        $fileTypes = FileType::with([
            'departments:id,title',
            'divisions:id,title',
            'units:id,title',
            'sections:id,title'
        ])->get();

        $metadatas = Metadata::all();

        // 6. Active Users (with placement, roles, permissions and capability bounding for User Permissions Matrix)
        $users = User::select('id', 'first_name', 'last_name', 'email', 'department_id', 'division_id', 'unit_id', 'section_id', 'designation_id', 'is_department_head', 'is_division_head', 'is_unit_head')
            ->with([
                'roles:id,name',
                'permissions:id,name',
                'designation:id,title,hierarchy_level,is_registry,is_head_of_office,grade_level',
                'department:id,title',
                'division:id,title',
                'unit:id,title',
                'section:id,title'
            ])
            ->where('is_approved', 1)
            ->orderBy('first_name', 'asc')
            ->get();

        $users->transform(function ($u) {
            $u->direct_permissions = $u->permissions->pluck('name')->toArray();
            $u->effective_permissions = PermissionRegistryService::getEffectiveUserPermissions($u);
            $u->allowed_modules = PermissionRegistryService::getAllowedModulesForUser($u);
            $u->active_capabilities = PermissionRegistryService::getActiveCapabilitiesForUser($u);
            return $u;
        });

        // 7. Executive Governance Statistics
        $totalDivisions = $departments->sum('division_count');
        $totalUnits = $departments->flatMap->division->sum('unit_count');
        $totalSections = $departments->flatMap->division->flatMap->unit->sum('sections_count');

        $stats = [
            'total_departments' => $departments->count(),
            'total_divisions' => $totalDivisions,
            'total_units' => $totalUnits,
            'total_sections' => $totalSections,
            'total_branches' => $departments->count() + $totalDivisions + $totalUnits + $totalSections,
            'total_designations' => $designations->count(),
            'total_registry_desks' => $designations->where('is_registry', true)->count(),
            'total_heads' => $designations->where('is_head_of_office', true)->count(),
            'total_roles' => $roles->count(),
            'total_permissions' => Permission::count(),
            'total_document_types' => $documentTypes->count(),
            'total_file_types' => $fileTypes->count(),
            'total_metadata_fields' => $metadatas->count(),
            'total_staff' => $users->count(),
        ];

        return inertia('settings/GovernanceHub', [
            'departments' => $departments,
            'designations' => $designations,
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
            'masterCatalog' => $masterCatalog,
            'capabilityModuleMap' => PermissionRegistryService::getCapabilityModuleMap(),
            'universalModules' => PermissionRegistryService::getUniversalModules(),
            'governanceModules' => PermissionRegistryService::getGovernanceModules(),
            'documentTypes' => $documentTypes,
            'fileTypes' => $fileTypes,
            'metadatas' => $metadatas,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Department Fast-Track Onboarding Wizard
     * Provisions Department + Sub-Divisions + Key Desks + Baseline Document Types in 1 transaction
     */
    public function onboardDepartment(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head_user_id' => 'nullable|exists:users,id',
            'divisions' => 'nullable|array',
            'divisions.*.title' => 'required|string|max:255',
            'divisions.*.description' => 'nullable|string',
            'divisions.*.units' => 'nullable|array',
            'include_registry_desk' => 'nullable|boolean',
            'include_director_desk' => 'nullable|boolean',
            'custom_desks' => 'nullable|array',
            'document_type_ids' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Create Department
            $dept = new Department();
            $dept->title = $request->title;
            $dept->description = $request->description ?? 'Department established under OAGF governance';
            $dept->head = $request->head_user_id ? 1 : 0;
            $dept->save();

            // 2. Attach Head of Department if provided
            if ($request->head_user_id) {
                User::where('id', $request->head_user_id)->update([
                    'department_id' => $dept->id,
                    'is_department_head' => 1
                ]);
            }

            // 3. Create Automated Functional Desks
            if ($request->boolean('include_director_desk', true)) {
                Designation::create([
                    'department_id' => $dept->id,
                    'hierarchy_level' => 'department',
                    'title' => 'Director (' . $dept->title . ')',
                    'cadre' => 'Executive Management',
                    'grade_level' => 17,
                    'description' => 'Head of Department / Accounting Director',
                    'is_registry' => false,
                    'is_head_of_office' => true,
                    'is_active' => true,
                ]);
            }

            if ($request->boolean('include_registry_desk', true)) {
                Designation::create([
                    'department_id' => $dept->id,
                    'hierarchy_level' => 'department',
                    'title' => 'Departmental Registry Officer (' . $dept->title . ')',
                    'cadre' => 'Executive Officer (Registry)',
                    'grade_level' => 10,
                    'description' => 'Supervises incoming/outgoing mails, file tracking, and cross-departmental dispatch',
                    'is_registry' => true,
                    'is_head_of_office' => false,
                    'is_active' => true,
                ]);
            }

            // Any extra custom desks
            if (!empty($request->custom_desks)) {
                foreach ($request->custom_desks as $desk) {
                    if (!empty($desk['title'])) {
                        Designation::create([
                            'department_id' => $dept->id,
                            'hierarchy_level' => 'department',
                            'title' => $desk['title'],
                            'cadre' => $desk['cadre'] ?? 'General Cadre',
                            'grade_level' => $desk['grade_level'] ?? 9,
                            'description' => $desk['description'] ?? null,
                            'is_registry' => !empty($desk['is_registry']),
                            'is_head_of_office' => !empty($desk['is_head_of_office']),
                            'is_active' => true,
                        ]);
                    }
                }
            }

            // 4. Create Divisions and Nested Units
            if (!empty($request->divisions)) {
                foreach ($request->divisions as $divData) {
                    $div = new Division();
                    $div->title = $divData['title'];
                    $div->description = $divData['description'] ?? null;
                    $div->department_id = $dept->id;
                    $div->head = 0;
                    $div->save();

                    // Auto Deputy Director / Division Head Desk
                    Designation::create([
                        'department_id' => $dept->id,
                        'division_id' => $div->id,
                        'hierarchy_level' => 'division',
                        'title' => 'Deputy Director / Head (' . $div->title . ')',
                        'cadre' => 'Administrative / Professional',
                        'grade_level' => 16,
                        'is_head_of_office' => true,
                        'is_registry' => false,
                        'is_active' => true,
                    ]);

                    // Units under this Division
                    if (!empty($divData['units'])) {
                        foreach ($divData['units'] as $unitData) {
                            $unitTitle = is_array($unitData) ? $unitData['title'] : $unitData;
                            if (!empty($unitTitle)) {
                                $unit = new Unit();
                                $unit->title = $unitTitle;
                                $unit->division_id = $div->id;
                                $unit->head = 0;
                                $unit->save();

                                // Unit Head desk
                                Designation::create([
                                    'department_id' => $dept->id,
                                    'division_id' => $div->id,
                                    'unit_id' => $unit->id,
                                    'hierarchy_level' => 'unit',
                                    'title' => 'Head of Unit (' . $unit->title . ')',
                                    'cadre' => 'Senior Officer',
                                    'grade_level' => 14,
                                    'is_head_of_office' => true,
                                    'is_registry' => false,
                                    'is_active' => true,
                                ]);
                            }
                        }
                    }
                }
            }

            // 5. Attach Document Types if specified
            if (!empty($request->document_type_ids)) {
                $dept->documentType()->sync($request->document_type_ids);
            }
        });

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Department and structural hierarchy fast-tracked and onboarded successfully!'
        ]);
    }

    /**
     * Synchronize authorized document types for a specific branch node (Department, Division, Unit, or Section)
     */
    public function syncBranchDocumentTypes(Request $request)
    {
        $request->validate([
            'branch_type' => 'required|in:department,division,unit,section',
            'branch_id' => 'required|integer',
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'integer|exists:document_types,id',
        ]);

        $modelClass = match($request->branch_type) {
            'department' => Department::class,
            'division' => Division::class,
            'unit' => Unit::class,
            'section' => Section::class,
        };

        $branch = $modelClass::findOrFail($request->branch_id);
        $branch->documentType()->sync($request->document_type_ids ?? []);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Authorized document classifications updated successfully for ' . ($branch->title ?? 'branch') . '.'
        ]);
    }

    /**
     * Synchronize branch associations for a given Document Type (Macro perspective)
     */
    public function syncDocumentTypeBranches(Request $request)
    {
        $request->validate([
            'document_type_id' => 'required|integer|exists:document_types,id',
            'department_ids' => 'nullable|array',
            'division_ids' => 'nullable|array',
            'unit_ids' => 'nullable|array',
            'section_ids' => 'nullable|array',
        ]);

        $docType = DocumentType::findOrFail($request->document_type_id);

        if ($request->has('department_ids')) {
            $docType->departments()->sync($request->department_ids ?? []);
        }
        if ($request->has('division_ids')) {
            $docType->divisions()->sync($request->division_ids ?? []);
        }
        if ($request->has('unit_ids')) {
            $docType->units()->sync($request->unit_ids ?? []);
        }
        if ($request->has('section_ids')) {
            $docType->sections()->sync($request->section_ids ?? []);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Branch assignments updated successfully for ' . $docType->name . '.'
        ]);
    }

    /**
     * Synchronize unified branch classifications (Document Types & File Types / Dockets)
     * for any structural node (Department, Division, Unit, or Section)
     */
    public function syncBranchClassifications(Request $request)
    {
        $request->validate([
            'branch_type' => 'required|in:department,division,unit,section',
            'branch_id' => 'required|integer',
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'integer|exists:document_types,id',
            'file_type_ids' => 'nullable|array',
            'file_type_ids.*' => 'integer|exists:file_types,id',
        ]);

        $modelClass = match($request->branch_type) {
            'department' => Department::class,
            'division' => Division::class,
            'unit' => Unit::class,
            'section' => Section::class,
        };

        $branch = $modelClass::findOrFail($request->branch_id);

        if ($request->has('document_type_ids')) {
            $branch->documentType()->sync($request->document_type_ids ?? []);
        }

        if ($request->has('file_type_ids')) {
            $branch->fileType()->sync($request->file_type_ids ?? []);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Office classifications (Document & File Types) synchronized successfully for ' . ($branch->title ?? 'office') . '.'
        ]);
    }

    /**
     * Synchronize branch associations for a given File Type / Docket (Macro perspective)
     */
    public function syncFileTypeBranches(Request $request)
    {
        $request->validate([
            'file_type_id' => 'required|integer|exists:file_types,id',
            'department_ids' => 'nullable|array',
            'division_ids' => 'nullable|array',
            'unit_ids' => 'nullable|array',
            'section_ids' => 'nullable|array',
        ]);

        $fileType = FileType::findOrFail($request->file_type_id);

        if ($request->has('department_ids')) {
            $fileType->departments()->sync($request->department_ids ?? []);
        }
        if ($request->has('division_ids')) {
            $fileType->divisions()->sync($request->division_ids ?? []);
        }
        if ($request->has('unit_ids')) {
            $fileType->units()->sync($request->unit_ids ?? []);
        }
        if ($request->has('section_ids')) {
            $fileType->sections()->sync($request->section_ids ?? []);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Administrative branch scope updated successfully for file type ' . $fileType->name . '.'
        ]);
    }

    /**
     * Create or update a File Type
     */
    public function createFileType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'headings' => 'nullable|array',
        ]);

        $fileType = $request->id ? FileType::findOrFail($request->id) : new FileType();
        $fileType->name = $request->name;
        $fileType->description = $request->description;
        $fileType->headings = $request->headings ?? ['Title', 'Description', 'Amount'];
        $fileType->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'File Type saved successfully.'
        ]);
    }

    /**
     * Delete a File Type
     */
    public function deleteFileType(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:file_types,id',
        ]);

        $fileType = FileType::findOrFail($request->id);
        $fileType->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'File Type deleted successfully.'
        ]);
    }
}
