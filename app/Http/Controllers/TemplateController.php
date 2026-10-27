<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use App\Models\Department;
use App\Services\DocumentReferenceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin') || $user->hasRole('super user');
        
        $templates = DocumentTemplate::with(['department', 'division', 'unit', 'section', 'documentType.metadata'])
            ->when(!$isSuperAdmin, function ($query) use ($user) {
                // If not super admin, show global templates and their department's templates
                $query->whereNull('department_id')
                      ->orWhere('department_id', $user->department_id);
            })
            ->latest()
            ->get();

        $documentTypes = DocumentType::with('metadata')->orderBy('name')->get();
        $departments = Department::with(['division.unit.section'])->orderBy('title')->get();

        return Inertia::render('Administration/Templates', [
            'templates' => $templates,
            'documentTypes' => $documentTypes,
            'departments' => $departments,
            'isSuperAdmin' => $isSuperAdmin,
            'userDepartmentId' => $user->department_id
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'document_type_id' => 'nullable|exists:document_types,id',
            'type' => 'nullable|string|max:50',
            'content_html' => 'required|string',
            'is_global' => 'boolean',
            'department_id' => 'nullable|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'unit_id' => 'nullable|exists:units,id',
            'section_id' => 'nullable|exists:sections,id',
            'reference_prefix' => 'nullable|string|max:100'
        ]);

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin') || $user->hasRole('super user');
        
        $isUniversal = (bool)($request->is_global || empty($request->department_id));
        $departmentId = $isUniversal ? null : ($request->department_id ?: $user->department_id);
        $divisionId = $isUniversal ? null : $request->division_id;
        $unitId = $isUniversal ? null : $request->unit_id;
        $sectionId = $isUniversal ? null : $request->section_id;

        $typeString = $request->type ?: 'memo';
        if ($request->document_type_id) {
            $docType = DocumentType::find($request->document_type_id);
            if ($docType) {
                $typeString = $docType->name;
            }
        }

        $referencePrefix = $request->reference_prefix ?: DocumentReferenceService::buildPrefix(
            $departmentId,
            $divisionId,
            $unitId,
            $sectionId
        );

        $template = DocumentTemplate::create([
            'title' => $request->title,
            'type' => $typeString,
            'document_type_id' => $request->document_type_id,
            'content_html' => $request->content_html,
            'is_universal' => $isUniversal,
            'department_id' => $departmentId,
            'division_id' => $divisionId,
            'unit_id' => $unitId,
            'section_id' => $sectionId,
            'reference_prefix' => $referencePrefix,
            'created_by' => $user->id,
            'version' => 1,
            'is_active' => true
        ]);

        return back()->with('success', 'Template "' . $template->title . '" created successfully with Reference Prefix [' . $referencePrefix . ']');
    }

    public function update(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin') || $user->hasRole('super user');

        // Check permission
        if (!$isSuperAdmin && $template->department_id !== $user->department_id) {
            return back()->withErrors(['message' => 'Unauthorized to edit this template.']);
        }

        $request->validate([
            'title' => 'required|string|max:191',
            'document_type_id' => 'nullable|exists:document_types,id',
            'type' => 'nullable|string|max:50',
            'content_html' => 'required|string',
            'is_global' => 'boolean',
            'department_id' => 'nullable|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'unit_id' => 'nullable|exists:units,id',
            'section_id' => 'nullable|exists:sections,id',
            'reference_prefix' => 'nullable|string|max:100'
        ]);

        $isUniversal = (bool)($request->is_global || empty($request->department_id));
        $departmentId = $isUniversal ? null : ($request->department_id ?: $template->department_id);
        $divisionId = $isUniversal ? null : $request->division_id;
        $unitId = $isUniversal ? null : $request->unit_id;
        $sectionId = $isUniversal ? null : $request->section_id;

        $typeString = $request->type ?: $template->type;
        if ($request->document_type_id) {
            $docType = DocumentType::find($request->document_type_id);
            if ($docType) {
                $typeString = $docType->name;
            }
        }

        $referencePrefix = $request->reference_prefix ?: DocumentReferenceService::buildPrefix(
            $departmentId,
            $divisionId,
            $unitId,
            $sectionId
        );

        $template->update([
            'title' => $request->title,
            'type' => $typeString,
            'document_type_id' => $request->document_type_id,
            'content_html' => $request->content_html,
            'is_universal' => $isUniversal,
            'department_id' => $departmentId,
            'division_id' => $divisionId,
            'unit_id' => $unitId,
            'section_id' => $sectionId,
            'reference_prefix' => $referencePrefix,
            'version' => $template->version + 1,
        ]);

        return back()->with('success', 'Template updated to version ' . $template->version);
    }

    public function destroy($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        if (!$isSuperAdmin && $template->department_id !== $user->department_id) {
            return back()->withErrors(['message' => 'Unauthorized to delete this template.']);
        }

        $template->delete();

        return back()->with('success', 'Template deleted successfully');
    }

    /**
     * Get list of active templates available to the current user.
     */
    public function getAvailable(Request $request)
    {
        $user = auth()->user();
        $type = $request->query('type');

        $templates = DocumentTemplate::where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('department_id');
                if ($user && $user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            })
            ->when($type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->orderBy('title')
            ->get(['id', 'title', 'type', 'version', 'department_id']);

        return response()->json($templates);
    }

    /**
     * Hydrate a specific document template with docket and user data.
     */
    public function hydrate(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $taskId = $request->query('task_id');
        $task = $taskId ? \App\Models\Task::with(['file.department', 'minutes.user.department'])->find($taskId) : null;
        $user = auth()->user();

        $hydrationService = app(\App\Services\TemplateHydrationService::class);
        $hydratedHtml = $hydrationService->hydrate($template->content_html, $task, $user, $request->all());

        return response()->json([
            'template_id' => $template->id,
            'title' => $template->title,
            'type' => $template->type,
            'hydrated_html' => $hydratedHtml
        ]);
    }
}

