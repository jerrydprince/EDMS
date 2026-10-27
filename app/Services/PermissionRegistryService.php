<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\EntityCapability;

class PermissionRegistryService
{
    /**
     * Master catalog of all EDMS modules and their granular permissions.
     */
    public static function getMasterCatalog(): array
    {
        return [
            'Payment' => [
                'payment view' => 'Access and view Finance & Accounts Operations Desk and payments list',
                'payment create' => 'Create or draft payment record',
                'payment update' => 'Edit or modify payment record',
                'payment delete' => 'Delete payment record',
                'payment initiate' => 'Initiate new payments from registry files',
                'payment approve' => 'Grant final executive approval for payment vouchers',
                'payment reject' => 'Reject or revert payments for correction/observation',
                'payment classify' => 'Classify payments into Advances, Recurrent, or Capital expenditure tracks',
            ],
            'Voucher' => [
                'voucher create' => 'Generate and prepare Treasury Form 1 payment vouchers',
                'voucher vat generate' => 'Generate VAT, WHT, and Stamp Duty triad deduction vouchers',
                'voucher update' => 'Edit payment voucher information',
                'voucher delete' => 'Delete payment voucher draft',
            ],
            'Checking' => [
                'checking stamp' => 'Verify arithmetic calculations, vote commitment, and affix Digital Checking Stamp',
            ],
            'Internal Audit' => [
                'audit stamp' => 'Affix Pre-Payment Internal Audit Clearance Seal',
                'audit query raise' => 'Issue formal statutory Pre-Payment Audit Query on file/payment',
                'audit query respond' => 'Submit formal response to an Audit Query',
            ],
            'Cashbook' => [
                'cashbook view' => 'Access Cash Book ledger and bank account balances',
                'cashbook create' => 'Create new cash book entry',
                'cashbook update' => 'Update cash book entry',
                'cashbook delete' => 'Delete cash book entry',
                'cashbook disburse' => 'Execute CBN TSA Mandate reference and disburse funds',
            ],
            'Votebook' => [
                'votebook view' => 'View departmental Vote Books and budget commitments',
                'votebook create' => 'Create new Vote Book account',
                'votebook update' => 'Update Vote Book sub-head details',
                'votebook delete' => 'Delete Vote Book account',
                'votebook edit' => 'Allocate budget sub-heads, record liabilities, and liquidate votes',
                'votebook encumber' => 'Encumber and commit budget funds on Vote Book',
            ],
            'Ledger' => [
                'ledger view' => 'View General Ledger and subsidiary ledgers',
                'ledger create' => 'Create new ledger entry or sub-ledger',
                'ledger update' => 'Update ledger record',
                'ledger delete' => 'Delete ledger record',
                'ledger post' => 'Post balanced dual-entry IPSAS journal vouchers to General Ledger',
            ],
            'Advances' => [
                'advances view' => 'View Staff Advances Ledger and monitor FR 1420 compliance',
                'advances create' => 'Issue new staff advance entry',
                'advances update' => 'Update staff advance details',
                'advances delete' => 'Cancel or remove staff advance',
                'advances retire' => 'Process, verify, and liquidate staff advance retirements',
            ],
            'Registry' => [
                'registry view' => 'View departmental and division file registries',
                'registry create' => 'Create new physical and electronic file jackets',
                'registry update' => 'Edit registry file metadata and index information',
                'registry delete' => 'Archive or remove registry files',
                'registry dispatch' => 'Dispatch files outward to other departments or external agencies',
                'admin registry view' => 'Central AGF Ministerial Registry master file intake and indexing',
            ],
            'Mails' => [
                'mails view' => 'View incoming and outgoing correspondence',
                'mails create' => 'Register and upload new incoming correspondence',
                'mails update' => 'Edit correspondence details and attachments',
                'mails delete' => 'Delete correspondence records',
                'mails minute' => 'Minute and route mail inward to officers or departments',
                'mails dispatch' => 'Dispatch external mails and record delivery receipts',
            ],
            'Workflow' => [
                'workflow view' => 'Access workflow task inbox and sent items',
                'workflow create' => 'Create and initiate new workflows',
                'workflow update' => 'Update workflow configurations and routes',
                'workflow delete' => 'Delete workflows',
                'workflow minute' => 'Minute, endorse, or forward a task to subordinate or peer',
                'workflow approve' => 'Approve non-financial departmental workflow tasks',
                'workflow return' => 'Return task to sender with observations',
                'workflow query' => 'Request additional information / raise query on a task',
                'workflow settings view' => 'Access and configure workflow engine settings',
                'workflow builder view' => 'Design and configure graphical workflow routes',
            ],
            'Edms' => [
                'document management view' => 'Access and browse organizational document branch hierarchy',
                'shared files view' => 'Access and view shared documents and files',
                'edms view' => 'Browse and view folders, files, and documents',
                'edms create' => 'Upload files and create document folders',
                'edms update' => 'Rename, move, and edit document metadata',
                'edms delete' => 'Move documents to recycle bin',
                'edms download' => 'Download document attachments and files',
                'edms preview' => 'Preview documents in the unified viewer',
                'edms version' => 'Create and manage document version revisions',
            ],
            'Archiving' => [
                'archiving view' => 'View archived documents and long-term storage records',
                'archiving create' => 'Archive documents for statutory retention',
                'archiving update' => 'Modify archiving retention policies',
                'archiving delete' => 'Purge archived records past retention schedule',
            ],
            'Pms' => [
                'pms view' => 'View staff Performance Management appraisals',
                'pms create' => 'Initiate appraisal cycles and KRA/KPI templates',
                'pms review' => 'Review and evaluate subordinate appraisal submissions',
                'pms update' => 'Update performance evaluation scores and reviews',
                'pms delete' => 'Remove appraisal records',
                'pms approve' => 'Approve and countersign annual performance ratings',
            ],
            'Servicom' => [
                'servicom view' => 'View Servicom customer service desk and feedback',
                'servicom create' => 'File citizen/staff complaints and service tracking',
                'servicom report' => 'Generate and export Servicom compliance reports',
                'servicom update' => 'Update complaint resolution status',
                'servicom delete' => 'Delete Servicom records',
            ],
            'Queries' => [
                'queries view' => 'View disciplinary and administrative staff queries',
                'queries create' => 'Issue formal administrative query to a staff member',
                'queries update' => 'Update query proceedings and hearing notes',
                'queries delete' => 'Dismiss or delete query records',
                'queries respond' => 'Submit formal written defense/response to a query',
                'queries verdict' => 'Issue final disciplinary verdict or exoneration',
            ],
            'Branch Setup' => [
                'branch setup view' => 'View organizational structure (Departments, Divisions, Units)',
                'branch setup create' => 'Create new departments, divisions, or units',
                'branch setup update' => 'Update branch details and assign branch heads',
                'branch setup delete' => 'Deactivate or remove organizational branches',
            ],
            'Manage Users' => [
                'manage users view' => 'View list of all staff user accounts',
                'manage users create' => 'Provision new staff accounts and set initial roles',
                'manage users update' => 'Edit user profile, placement, and permissions',
                'manage users delete' => 'Deactivate or delete user accounts',
            ],
            'Access Config' => [
                'access config view' => 'View access level roles and permissions matrix',
                'access config create' => 'Create new access level roles and custom permissions',
                'access config update' => 'Update permissions assigned to roles or departments',
                'access config delete' => 'Delete custom roles',
                'working calendar view' => 'Access and manage the civil service working calendar',
                'document templates view' => 'View and manage statutory document templates',
            ],
            'Support' => [
                'support view' => 'Access and view Support Helpdesk, FAQs, SOP guides, and own tickets',
                'support create' => 'Submit and log technical support assistance tickets',
                'support update' => 'Configure support contact info and author/edit FAQs and SOP documentation',
                'support delete' => 'Delete FAQs and SOP documentation',
                'support dispatch' => 'Access IT Dispatcher triage desk, reassign tickets, and update status',
            ],
            'Settings' => [
                'settings view' => 'Access personal settings, user profile, and interface preferences',
                'settings organisation' => 'Manage organisation metadata, corporate branding, and stamps',
                'settings security' => 'Configure password policies, 2FA enforcement, and session limits',
                'settings system' => 'Manage enterprise system configurations and developer telemetry',
                'settings integrations' => 'Configure third-party integrations, OAuth apps, and webhooks',
            ],
            'Notifications' => [
                'notifications view' => 'View notification alerts, tasks, and system activity logs',
                'notifications manage' => 'Mark notifications as read/unread, delete alerts, and configure preferences',
            ],
            'Audit' => [
                'audit view' => 'View system-wide security, file, and financial audit logs',
                'audit export' => 'Export forensic audit evidence reports to CSV',
                'audit verify' => 'Execute cryptographic hash-chain integrity verification',
                'audit create' => 'Create or export audit records',
                'audit update' => 'Configure audit retention policies',
                'audit delete' => 'Purge archived audit logs past statutory retention',
            ],
            'Recycle Bin' => [
                'recycle bin view' => 'View deleted documents, tasks, mails, and system entities',
                'recycle bin update' => 'Restore deleted documents and entities to active storage',
                'recycle bin delete' => 'Permanently destroy and purge deleted items from vault',
            ],
            'Analytics' => [
                'analytics view' => 'View executive dashboard metrics and processing KPIs',
                'analytics create' => 'Generate custom executive intelligence reports',
                'analytics update' => 'Customize reporting charts and widgets',
                'analytics delete' => 'Delete saved custom report templates',
            ],
            'Chat' => [
                'chat view' => 'Access internal instant messaging and task discussions',
                'chat create' => 'Send direct messages and participate in channels',
                'chat update' => 'Edit sent messages and pin announcements',
                'chat delete' => 'Delete messages and leave channels',
            ],
            'Document Type' => [
                'document type view' => 'View document classification types',
                'document type create' => 'Define new document categories and templates',
                'document type update' => 'Modify document classifications',
                'document type delete' => 'Delete unused document types',
            ],
            'National Pool Management' => [
                'pool view' => 'Access National Pool Census (GL 08-17 across all MDAs) and requisitions',
                'pool create' => 'Register pool officers and log incoming MDA requisitions',
                'pool update' => 'Update pool officer competencies, matching candidates and rotational notes',
                'pool post' => 'Draft, vet, and approve official posting orders (Form OAGF/POOL/01)',
                'pool assumption' => 'Record, verify, and file returned MDA Assumption of Duty Slips',
                'pool report' => 'Export National Census, Federal Character quota analytics, and quarterly pool reports',
            ],
            'Administration & HR' => [
                'appointment view' => 'Access nominal roll, open/secret personal files, and 24-month confirmation alerts',
                'appointment confirm' => 'Process and approve 24-month confirmation of appointment (PSR 020301)',
                'appointment discipline' => 'Issue PSR 100302 queries, manage panels, and track 45-day disciplinary lifecycle',
                'appointment secondment' => 'Process transfers of service, secondments, and acting appointment briefs',
                'promotion view' => 'Access APER repository, confidential scores, and promotion rosters',
                'promotion aper manage' => 'Issue annual call circulars, validate and collect APER submissions (Appendix D)',
                'promotion brief' => 'Compile and generate FCSC / Senior Staff Committee (SSC) promotion briefs',
                'promotion conversion' => 'Process cadre conversions, advancements, and upgradings (PSR 020909)',
                'training view' => 'Access Training Needs Assessment (TNA) and staff training histories',
                'training plan' => 'Compile Annual Training Plan and budget, issue sponsorship letters',
                'welfare view' => 'View staff welfare records (NHIS, Pension, Housing loans)',
                'welfare approve' => 'Process and approve compassionate, medical, and burial grants (48-hr SLA)',
                'admin registry view' => 'Access departmental sub-registry inward/outward registers (Appendix B)',
                'admin registry dispatch' => 'Dispatch files with File Movement Card (Appendix A) and monitor 24-hr table SLAs',
                'admin registry stamp' => 'Affix official digital registry stamp or directorate executive seal',
            ],
            'Metadata Setup' => [
                'metadata setup view' => 'View custom metadata schema fields',
                'metadata setup create' => 'Create custom metadata attributes for indexing',
                'metadata setup update' => 'Modify metadata definitions',
                'metadata setup delete' => 'Remove metadata attributes',
            ],
            'Department' => [
                'department view' => 'View department records and staff lists',
                'department create' => 'Create new department',
                'department update' => 'Update department details',
                'department delete' => 'Delete department',
            ],
            'Division' => [
                'division view' => 'View division records and staff lists',
                'division create' => 'Create new division',
                'division update' => 'Update division details',
                'division delete' => 'Delete division',
            ],
            'Unit' => [
                'unit view' => 'View unit records and staff lists',
                'unit create' => 'Create new unit',
                'unit update' => 'Update unit details',
                'unit delete' => 'Delete unit',
            ],
        ];
    }

    /**
     * Synchronize all permissions idempotently into the database.
     * Inserts any missing permission without deleting or modifying existing ones.
     */
    public static function syncAll(): array
    {
        $catalog = self::getMasterCatalog();
        $created = 0;
        $existing = 0;

        foreach ($catalog as $group => $permissions) {
            foreach ($permissions as $name => $description) {
                $perm = Permission::firstOrCreate(
                    ['name' => strtolower(trim($name)), 'guard_name' => 'web']
                );

                if ($perm->wasRecentlyCreated) {
                    $created++;
                } else {
                    $existing++;
                }
            }
        }

        // Assign baseline permissions to standard roles
        self::assignBaselineRolePresets();

        return [
            'created' => $created,
            'existing' => $existing,
            'total' => $created + $existing
        ];
    }

    /**
     * Assign standard baseline permissions to standard OAGF roles.
     * Preserves existing assignments and only adds missing baseline permissions.
     */
    public static function assignBaselineRolePresets(): void
    {
        // 1. Super Admin: ALL permissions
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        // 2. Admin: Core Document & System Admin Permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPerms = Permission::where(function ($q) {
                $q->where('name', 'like', 'edms%')
                  ->orWhere('name', 'like', 'manage users%')
                  ->orWhere('name', 'like', 'branch setup%')
                  ->orWhere('name', 'like', 'document type%')
                  ->orWhere('name', 'like', 'metadata setup%')
                  ->orWhere('name', 'like', 'access config%')
                  ->orWhere('name', 'like', 'audit%')
                  ->orWhere('name', 'like', 'recycle bin%')
                  ->orWhere('name', 'like', 'archiving%')
                  ->orWhere('name', 'like', 'department%')
                  ->orWhere('name', 'like', 'division%')
                  ->orWhere('name', 'like', 'unit%')
                  ->orWhere('name', 'like', 'support%')
                  ->orWhere('name', 'like', 'settings%')
                  ->orWhere('name', 'like', 'notifications%');
            })->get();
            $admin->givePermissionTo($adminPerms);
        }

        // 3. Super User: Cross-departmental operational supervisor
        $superUser = Role::where('name', 'super user')->first();
        if ($superUser) {
            $superUserPerms = Permission::whereIn('name', [
                'edms view', 'edms preview', 'edms download', 'edms create', 'edms update', 'edms version',
                'shared files view', 'document management view',
                'mails view', 'mails create', 'mails update', 'mails minute', 'mails dispatch',
                'workflow view', 'workflow minute', 'workflow approve', 'workflow return',
                'registry view', 'registry create', 'registry update', 'registry dispatch',
                'support view', 'support create', 'support dispatch',
                'settings view',
                'notifications view', 'notifications manage',
                'audit view', 'audit export',
                'recycle bin view', 'recycle bin update',
                'analytics view',
                'chat view', 'chat create'
            ])->get();
            $superUser->givePermissionTo($superUserPerms);
        }

        // 4. Director: Executive & Departmental Action Permissions
        $director = Role::where('name', 'director')->first();
        if ($director) {
            $directorPerms = Permission::whereIn('name', [
                'edms view', 'edms preview', 'edms download',
                'mails view', 'mails minute',
                'workflow view', 'workflow minute', 'workflow approve', 'workflow return',
                'registry view',
                'payment view', 'payment approve', 'payment reject',
                'votebook view', 'cashbook view', 'ledger view', 'advances view',
                'pms view', 'pms approve',
                'queries view', 'queries create', 'queries verdict', 'servicom view',
                'support view', 'support create',
                'settings view',
                'notifications view', 'notifications manage',
                'analytics view',
                'chat view', 'chat create'
            ])->get();
            $director->givePermissionTo($directorPerms);
        }

        // 5. Deputy Director: Division Head Permissions
        $deputyDirector = Role::where('name', 'deputy director')->first();
        if ($deputyDirector) {
            $ddPerms = Permission::whereIn('name', [
                'edms view', 'edms preview', 'edms download', 'edms create',
                'mails view', 'mails minute',
                'workflow view', 'workflow minute', 'workflow approve', 'workflow return',
                'registry view',
                'payment view', 'payment classify', 'payment reject',
                'votebook view', 'votebook edit',
                'cashbook view', 'ledger view', 'advances view',
                'pms view', 'pms review',
                'queries view', 'queries respond',
                'support view', 'support create',
                'settings view',
                'notifications view', 'notifications manage',
                'chat view', 'chat create'
            ])->get();
            $deputyDirector->givePermissionTo($ddPerms);
        }

        // 6. Assistant Director: Unit Head Permissions
        $assistantDirector = Role::where('name', 'assistant director')->first();
        if ($assistantDirector) {
            $adPerms = Permission::whereIn('name', [
                'edms view', 'edms preview', 'edms download', 'edms create',
                'mails view', 'mails minute',
                'workflow view', 'workflow minute', 'workflow return',
                'registry view',
                'payment view',
                'voucher create', 'voucher vat generate',
                'votebook view', 'votebook edit',
                'cashbook view', 'ledger view', 'ledger post', 'advances view', 'advances retire',
                'pms view',
                'queries view', 'queries respond',
                'support view', 'support create',
                'settings view',
                'notifications view', 'notifications manage',
                'chat view', 'chat create'
            ])->get();
            $assistantDirector->givePermissionTo($adPerms);
        }

        // 7. Record Manager: Full Registry & Inward Mail Processing
        $recordManager = Role::where('name', 'record manager')->first();
        if ($recordManager) {
            $rmPerms = Permission::whereIn('name', [
                'edms view', 'edms preview', 'edms download', 'edms create',
                'registry view', 'registry create', 'registry update', 'registry dispatch',
                'mails view', 'mails create', 'mails update', 'mails minute', 'mails dispatch',
                'workflow view', 'workflow minute',
                'payment view', 'payment initiate',
                'recycle bin view', 'recycle bin update',
                'support view', 'support create',
                'settings view',
                'notifications view',
                'chat view', 'chat create'
            ])->get();
            $recordManager->givePermissionTo($rmPerms);
        }

        // 8. AGF Registry: Ministerial Central Intake
        $agfRegistry = Role::where('name', 'AGF Registry')->orWhere('name', 'agf registry')->first();
        if ($agfRegistry) {
            $agfPerms = Permission::whereIn('name', [
                'admin registry view', 'registry view', 'registry create', 'registry update', 'registry dispatch',
                'mails view', 'mails create', 'mails update', 'mails minute', 'mails dispatch',
                'edms view', 'edms preview', 'edms download', 'edms create',
                'workflow view', 'workflow minute',
                'support view', 'support create',
                'settings view',
                'notifications view',
                'chat view', 'chat create'
            ])->get();
            $agfRegistry->givePermissionTo($agfPerms);
        }

        // 9. Regular User: General Desk Officer
        $regularUser = Role::where('name', 'regular user')->first();
        if ($regularUser) {
            $ruPerms = Permission::whereIn('name', [
                'edms view', 'edms preview', 'edms download',
                'mails view',
                'workflow view', 'workflow minute', 'workflow return',
                'queries view', 'queries respond',
                'support view', 'support create',
                'settings view',
                'notifications view',
                'chat view', 'chat create'
            ])->get();
            $regularUser->givePermissionTo($ruPerms);
        }
    }

    /**
     * Dynamically register a new custom permission on the fly.
     */
    public static function createCustomPermission(string $module, string $action, ?string $description = null): Permission
    {
        $permName = strtolower(trim($module . ' ' . $action));
        $perm = Permission::firstOrCreate(
            ['name' => $permName, 'guard_name' => 'web']
        );

        // Automatically assign newly created dynamic permission to Super Admin
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin && !$superAdmin->hasPermissionTo($perm)) {
            $superAdmin->givePermissionTo($perm);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Illuminate\Support\Facades\Cache::flush();

        return $perm;
    }

    /**
     * Return all registered permissions grouped by module with metadata.
     */
    public static function getGroupedPermissions(): array
    {
        $all = Permission::orderBy('name')->get();
        $catalog = self::getMasterCatalog();
        $grouped = [];

        foreach ($catalog as $group => $perms) {
            $grouped[$group] = [];
            foreach ($perms as $permName => $desc) {
                $p = $all->firstWhere('name', $permName);
                if ($p) {
                    $grouped[$group][] = [
                        'id' => $p->id,
                        'name' => $p->name,
                        'description' => $desc,
                    ];
                }
            }
        }

        // Include any custom created permissions not in the master catalog under 'Custom / Other'
        $catalogKeys = [];
        foreach ($catalog as $perms) {
            foreach ($perms as $k => $v) {
                $catalogKeys[] = $k;
            }
        }

        $custom = $all->whereNotIn('name', $catalogKeys);
        if ($custom->isNotEmpty()) {
            $grouped['Custom / Dynamic'] = $custom->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => 'Custom registered permission',
                ];
            })->values()->all();
        }

        return $grouped;
    }

    /**
     * Returns recommended baseline permissions for a given functional designation title
     */
    public static function getDesignationPresets(string $designationTitle): array
    {
        $title = strtolower(trim($designationTitle));

        if (str_contains($title, 'checking')) {
            return ['payment view', 'checking stamp', 'voucher update', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'audit')) {
            return ['payment view', 'audit stamp', 'audit query raise', 'audit query respond', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'cashier')) {
            return ['payment view', 'cashbook view', 'cashbook create', 'cashbook update', 'cashbook disburse', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'vote book') || str_contains($title, 'votebook')) {
            return ['payment view', 'votebook view', 'votebook create', 'votebook update', 'votebook edit', 'voucher create', 'voucher vat generate', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'final accounts') || str_contains($title, 'ledger')) {
            return ['payment view', 'ledger view', 'ledger create', 'ledger update', 'ledger post', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'advances')) {
            return ['payment view', 'advances view', 'advances create', 'advances retire', 'ledger view', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'central registry')) {
            return ['admin registry view', 'registry view', 'registry create', 'registry update', 'registry dispatch', 'mails view', 'mails create', 'mails update', 'mails minute', 'mails dispatch', 'edms view', 'edms preview', 'edms download', 'edms create', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'registry')) {
            return ['registry view', 'registry create', 'registry update', 'registry dispatch', 'mails view', 'mails create', 'mails update', 'mails minute', 'mails dispatch', 'edms view', 'edms preview', 'edms download', 'edms create', 'workflow view', 'workflow minute'];
        }
        if (str_contains($title, 'director') || str_contains($title, 'hod')) {
            return ['edms view', 'edms preview', 'edms download', 'edms create', 'mails view', 'mails minute', 'mails dispatch', 'workflow view', 'workflow minute', 'workflow approve', 'workflow return', 'registry view', 'payment view', 'payment approve', 'payment reject', 'voucher create', 'votebook view', 'cashbook view', 'ledger view', 'pms view', 'pms review', 'pms approve', 'queries view', 'queries create', 'chat view', 'chat create'];
        }

        return ['edms view', 'edms preview', 'edms download', 'mails view', 'workflow view', 'workflow minute', 'chat view', 'chat create'];
    }

    /**
     * Map system capability codes from system_capabilities table to master catalog module names.
     */
    public static function getCapabilityModuleMap(): array
    {
        return [
            // Financial Management
            'payment_vouchers' => ['Payment', 'Voucher', 'Checking', 'Advances'],
            'vote_book_accounting' => ['Votebook', 'Ledger'],
            'cash_book_maintenance' => ['Cashbook'],
            'mandate_generation' => ['Payment'],
            'bank_reconciliation' => ['Cashbook'],
            'tax_deductions_wht_vat' => ['Voucher'],

            // Audit & Inspection
            'pre_payment_audit' => ['Internal Audit'],
            'post_payment_inspection' => ['Internal Audit'],
            'audit_query_issuance' => ['Internal Audit', 'Queries'],

            // Public Procurement
            'procurement_requisitions' => ['Procurement'],
            'tender_evaluation' => ['Procurement'],

            // National Pool Management
            'pool_officers_census' => ['National Pool Management'],
            'pool_requisitions_postings' => ['National Pool Management'],

            // Administration & HR
            'nominal_roll_mgt' => ['Administration & HR'],
            'leave_administration' => ['Administration & HR'],
            'psr_disciplinary_query' => ['Queries'],
            'appointment_confirmation' => ['Administration & HR'],
            'promotion_aper_management' => ['Administration & HR'],
            'training_welfare_management' => ['Administration & HR'],
            'departmental_sub_registries' => ['Administration & HR', 'Registry'],

            // Registry & Records
            'file_jacket_creation' => ['Registry'],
            'document_scanning_upload' => ['Edms', 'Archiving'],
            'cross_branch_dispatch' => ['Registry'],

            // Executive Oversight
            'executive_oversight' => ['Analytics'],
            'executive_clearance' => ['Workflow'],
            'file_call_up' => ['Workflow'],
        ];
    }

    /**
     * Universal base operational modules accessible to all active civil servants.
     */
    public static function getUniversalModules(): array
    {
        return [
            'Workflow',
            'Mails',
            'Pms',
            'Servicom',
            'Chat',
            'Support',
            'Settings',
            'Notifications',
            'Recycle Bin'
        ];
    }

    /**
     * Governance & structural modules reserved for administrative governance and IT controllers.
     */
    public static function getGovernanceModules(): array
    {
        return [
            'Branch Setup',
            'Manage Users',
            'Access Config',
            'Department',
            'Division',
            'Unit',
            'Metadata Setup',
            'Document Type',
            'Audit'
        ];
    }

    /**
     * Retrieve active capability codes for a user based on their 4-tier placement in the organogram.
     * Evaluates Section -> Unit -> Division -> Department hierarchy.
     */
    public static function getActiveCapabilitiesForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }

        // Determine hierarchy node
        $nodes = [];
        if ($user->section_id) {
            $nodes[] = ['type' => 'section', 'id' => $user->section_id];
        }
        if ($user->unit_id) {
            $nodes[] = ['type' => 'unit', 'id' => $user->unit_id];
        }
        if ($user->division_id) {
            $nodes[] = ['type' => 'division', 'id' => $user->division_id];
        }
        if ($user->department_id) {
            $nodes[] = ['type' => 'department', 'id' => $user->department_id];
        }

        // Find the most specific active capabilities configured in entity_capabilities
        foreach ($nodes as $node) {
            $caps = EntityCapability::where('entity_type', $node['type'])
                ->where('entity_id', $node['id'])
                ->where('is_active', true)
                ->pluck('capability_code')
                ->toArray();

            if (!empty($caps)) {
                return $caps;
            }
        }

        // Fallback: If no direct node records, return department active capabilities
        if ($user->department_id) {
            return EntityCapability::where('entity_type', 'department')
                ->where('entity_id', $user->department_id)
                ->where('is_active', true)
                ->pluck('capability_code')
                ->toArray();
        }

        return [];
    }

    /**
     * Resolve the allowed module names for a user based on active capabilities & governance roles.
     */
    public static function getAllowedModulesForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $catalog = self::getMasterCatalog();

        // Super Admin has global enterprise access across all 28 modules
        if ($user->hasRole('super admin')) {
            return array_keys($catalog);
        }

        // Start with universal base modules
        $allowed = self::getUniversalModules();

        // Admins can manage governance modules
        if ($user->hasAnyRole(['admin', 'administrator', 'system admin'])) {
            $allowed = array_merge($allowed, self::getGovernanceModules());
        }

        // Add modules unlocked by active departmental capabilities
        $activeCaps = self::getActiveCapabilitiesForUser($user);
        $capMap = self::getCapabilityModuleMap();

        foreach ($activeCaps as $cap) {
            if (isset($capMap[$cap])) {
                $allowed = array_merge($allowed, $capMap[$cap]);
            }
        }

        return array_values(array_unique($allowed));
    }

    /**
     * Return effective permissions for a user bounded strictly by their department's active capabilities.
     */
    public static function getEffectiveUserPermissions(?User $user): array
    {
        if (!$user) {
            return [];
        }

        // Super Admin maintains global permissions without capability bounding
        if ($user->hasRole('super admin')) {
            return $user->getAllPermissions()->pluck('name')->toArray();
        }

        // Check if user has explicit permissions configured in the Civil Service User Permissions Matrix
        $directPerms = $user->permissions()->pluck('name')->toArray();
        if (!empty($directPerms)) {
            // Explicit user permissions are the primary source of truth
            $basePerms = $directPerms;
        } else {
            // Fallback to role permissions if no individual user permissions have been configured yet
            $basePerms = $user->getAllPermissions()->pluck('name')->toArray();
        }

        $allowedModules = self::getAllowedModulesForUser($user);
        $catalog = self::getMasterCatalog();

        $effective = [];
        foreach ($catalog as $moduleName => $permissions) {
            if (in_array($moduleName, $allowedModules)) {
                foreach (array_keys($permissions) as $permName) {
                    if (in_array($permName, $basePerms)) {
                        $effective[] = $permName;
                    }
                }
            }
        }

        return array_values(array_unique($effective));
    }

    /**
     * Returns grouped permissions containing ONLY the active modules allowed for this user.
     * Used for the Governance Hub User Permissions Matrix.
     */
    public static function getGroupedPermissionsForUser(?User $user): array
    {
        $allGrouped = self::getGroupedPermissions();
        if (!$user) {
            return $allGrouped;
        }

        $allowedModules = self::getAllowedModulesForUser($user);

        $filtered = [];
        foreach ($allGrouped as $moduleName => $perms) {
            if (in_array($moduleName, $allowedModules)) {
                $filtered[$moduleName] = $perms;
            }
        }

        return $filtered;
    }

    /**
     * Check if a specific permission ability is allowed under the user's active capabilities.
     */
    public static function isPermissionAllowedForUser(User $user, string $permissionName): bool
    {
        if ($user->hasRole('super admin')) {
            return true;
        }

        $perm = strtolower(trim($permissionName));
        $catalog = self::getMasterCatalog();
        $targetModule = null;

        foreach ($catalog as $moduleName => $perms) {
            if (array_key_exists($perm, $perms)) {
                $targetModule = $moduleName;
                break;
            }
        }

        // If the permission is not part of any capability module (e.g. unknown or universal), allow evaluation
        if (!$targetModule) {
            return true;
        }

        $allowedModules = self::getAllowedModulesForUser($user);
        return in_array($targetModule, $allowedModules);
    }
}

