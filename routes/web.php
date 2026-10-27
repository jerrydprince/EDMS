<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\MetadataController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\GovernanceHubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\WorkFlowController;
use App\Http\Controllers\WorkflowBuilderController;
use App\Http\Controllers\WorkflowSettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\RegistryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PMSController;
use App\Http\Controllers\KRAController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\CorrespondenceController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CashBookController;
use App\Http\Controllers\RecycleBinController;
use App\Http\Controllers\FandAController;
use App\Http\Controllers\VoteBookController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WorkingCalendarController;
use App\Http\Controllers\StreamlinedTaskWorkflowController;
use App\Http\Controllers\StaffQueryController;
use App\Http\Controllers\ServicomController;
use App\Http\Controllers\DocumentGenerationController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\DepartmentHubController;
use App\Http\Controllers\PoolManagementController;
use App\Http\Controllers\AdministrationDepartmentController;
use App\Http\Controllers\GenericWorkflowInstanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    return redirect()->to('/login');
});

//login
Route::middleware('guest')->group(function(){
    //Route::get('/login',[LoginController::class, 'login'])->name('login');
    //Route::post('/login', [LoginController::class, 'authenticate'])->name('post-login');
    Route::get('/register', [RegistrationController::class, 'index'])->name('registration');
    Route::post('/register', [RegistrationController::class, 'register']);
    Route::get('/resend-verification-code', [RegistrationController::class, 'resendVerificationCode']);
    Route::post('/email-verification', [RegistrationController::class, 'emailVerifiation']);
    Route::get('/email-exist', [RegistrationController::class, 'checkMailExit']);
    Route::get('/phone-exist', [RegistrationController::class, 'checkPhoneExist']);
    Route::get('/password/reset/{email}', [PasswordController::class, 'passwordReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'ResetUserPassword']);
    Route::get('/password/recovery', [PasswordController::class, 'getPasswordResetPage'])->name('password.recovery');
    Route::post('/user-forgot-password', [PasswordController::class, 'passwordRecovery'])->name('user.forgot.password');
});


Route::middleware(['auth','check2fastatus'])->group(function () {
    // Route::prefix('admin')->middleware('role:super admin| role:admin')->group(function(){
        
        
    // });

    Route::post('/forgot-password', [PasswordController::class, 'passwordRecovery']);

    Route::get('/metrics', function() {
        $user = auth()->user();
        if (!$user) return response()->json([
            'workload' => 0, 'pending' => 0, 'due' => 0, 'overdue' => 0, 'completed' => 0, 'returned' => 0, 'dispatched' => 0
        ]);
        
        $workload = \App\Models\TaskAssignment::where('assigned_to', $user->id)->where('status', '!=', 'completed')->count();
        $pending = \App\Models\TaskAssignment::where('assigned_to', $user->id)->where('status', 'pending')->count();
        $completed = \App\Models\TaskAssignment::where('assigned_to', $user->id)->where('status', 'completed')->count();
        $returned = \App\Models\TaskAssignment::where('assigned_to', $user->id)->where('status', 'revision')->count();
        
        return response()->json([
            'workload' => $workload,
            'pending' => $pending,
            'due' => 0,
            'overdue' => 0,
            'completed' => $completed,
            'returned' => $returned,
            'dispatched' => 0,
        ]);
    });

        //archiving
       // Route::get('/archiving/{id}', [ArchiveController::class, 'Archiving']);
        Route::post('/upload-files', [ArchiveController::class, 'uploadFiles'])->middleware('permission:edms create');
        Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles'])->middleware('permission:edms create');
        Route::get('/delete-archive', [ArchiveController::class, 'deleteArchives'])->middleware('permission:edms delete');
        Route::get('/download-file', [ArchiveController::class, 'downloadFile'])->middleware('permission:edms view');
        Route::post('/duplicate-file', [ArchiveController::class, 'DuplicateFile'])->middleware('permission:edms view');
        Route::post('/change-file-format', [ArchiveController::class, 'changeFileFormat'])->middleware('permission:edms update');
        Route::post('/upload-new-document', [ArchiveController::class, 'uploadNewDocument'])->middleware('permission:edms create');
        Route::post('/restore-file-version', [ArchiveController::class, 'restoreFileVersion'])->middleware('permission:edms update');
        Route::post('/lock-document', [ArchiveController::class, 'lockDocument'])->middleware('permission:edms update');
        Route::get('/delete-file-version', [ArchiveController::class, 'deleteFileVersion'])->middleware('permission:edms delete');
        Route::get('/share-file-search', [ArchiveController::class, 'shareFileSearch'])->middleware('permission:edms view');
        Route::post('/share-file', [ArchiveController::class, 'shareFile'])->middleware('permission:edms view');

        // Document Templates Root Aliases (Unprefixed)
        Route::get('/document-templates/get', [DocumentGenerationController::class, 'getTemplates']);
        Route::post('/document-templates/generate-file', [DocumentGenerationController::class, 'generateStandaloneFile']);
        Route::post('/document-templates/preview-html', [DocumentGenerationController::class, 'previewHtml']);
        Route::post('/task/{task}/document/generate', [DocumentGenerationController::class, 'generate']);
        Route::get('/templates/{id}/hydrate', [TemplateController::class, 'hydrate']);
        Route::get('/templates/available', [TemplateController::class, 'getAvailable']);
        Route::post('/directorates/convert-template-file', [DepartmentHubController::class, 'convertTemplateFile']);
        Route::post('/templates/convert-template-file', [DepartmentHubController::class, 'convertTemplateFile']);

        Route::prefix('edms')->group(function(){
            Route::middleware('permission:edms view')->group(function () {
                Route::get('/', [DashboardController::class, 'index'])->name('edms');
                Route::get('/branch-overview', [DashboardController::class, 'BranchesOverview']);
    
                //archiving
                Route::get('/archiving/{branch}/{folder_id}', [ArchiveController::class, 'Archiving']);
                Route::get('/folder-view/{branch}/{id}', [ArchiveController::class, 'folderView'])->name('folder-view');
                Route::get('/create-folder',[ArchiveController::class, 'createFolder']);
                Route::post('/close-folder/{id}', [ArchiveController::class, 'closeFolder']);
                Route::post('/rename-file', [ArchiveController::class, 'renameFile']);
                Route::post('/update-branch-metadata', [ArchiveController::class, 'updateBranchMetadatas']);
    
                //branches
                Route::get('/branches', [ArchiveController::class, 'userBranches']);
                Route::get('/delete-folder/{id}', [ArchiveController::class, 'deleteFolder']);
    
                //support
                Route::get('/support', function() {
                    return redirect('/dashboard/support');
                });
    
                //file type
                Route::get('/get-branch-file-type', [ArchiveController::class, 'getBranchFileType']);
    
                //metadata
                Route::get('/metadatas', [MetadataController::class, 'getMetadata']);
                Route::get('/branch-metdata', [MetadataController::class, 'getBranchMetadata']);
    
                //users
                Route::post('/update-profile-image', [UserController::class, 'updateProfileImage']);

                //chat heartbeat root fallbacks
                Route::post('/chat/heartbeat', [ChatController::class, 'heartbeat'])->middleware('permission:chat view');
                Route::post('/chat/leave', [ChatController::class, 'leaveChat'])->middleware('permission:chat view');
            });
        });

        //dashboard
        Route::prefix('dashboard')->group(function(){
            
            /**
             * pasted old admin route here
             */
            //Route::middleware()->group(function () {
                Route::get('/',[AdminController::class, 'index'])->name('admin.dashboard');
                Route::get('/branch-setup', function() {
                    return redirect('/dashboard/governance-hub?tab=branches');
                })->middleware('permission:branch setup view|access config view');
                Route::get('/metadata-setup', function() {
                    return redirect('/dashboard/governance-hub?tab=metadata');
                })->middleware('permission:metadata setup view|access config view');
            
                Route::post('/create-department-division-unit', [AdminController::class, 'CreateDepartmentDivisionUnit'])->middleware('permission:branch setup create');
                Route::post('/delete-department', [AdminController::class, 'deleteDepartment'])->middleware('permission:branch setup delete');
                //Route::post('/duplicate-department', [AdminController::class, 'duplicateDepartment']);
                Route::get('/get-divisions', [AdminController::class, 'getDivision'])->middleware('permission:branch setup create');
                Route::post('/delete-division', [AdminController::class, 'deleteDivision'])->middleware('permission:branch setup create');
            // Route::post('/duplicate-division', [AdminController::class, 'duplicateDivision']);
                Route::post('/add-document-type-to-department', [AdminController::class, 'addDocumentTypeToDepartment'])->middleware('permission:branch setup create');
                Route::post('/add-document-type-to-division', [AdminController::class, 'addDocumentTypeToDivision'])->middleware('permission:branch setup create');
                Route::post('/add-document-type-to-unit', [AdminController::class, 'addDocumentTypeToUnit'])->middleware('permission:branch setup create');
                Route::post('/add-document-type-to-section', [AdminController::class, 'addDocumentTypeToSection'])->middleware('permission:branch setup create');
                Route::post('/add-division-to-department', [AdminController::class, 'AddDivisionToDepartment'])->middleware('permission:branch setup create');
                Route::post('/edit-department', [AdminController::class, 'editDepartment'])->middleware('permission:branch setup create');
                Route::get('/get-units', [AdminController::class, 'getUnits'])->middleware('permission:branch setup create');
                Route::post('/delete-unit', [AdminController::class, 'deleteUnit'])->middleware('permission:branch setup create');
                //Route::post('/duplicate-unit', [AdminController::class, 'duplicateUnit']);
                Route::post('/add-unit-to-division', [AdminController::class, 'addUnitToDivision'])->middleware('permission:branch setup create');
                Route::post('/edit-division', [AdminController::class, 'editDivision'])->middleware('permission:branch setup create');
                Route::post('/edit-unit', [AdminController::class,'editUnit'])->middleware('permission:branch setup create');
                Route::get('/search-department-head', [AdminController::class, 'searchDepartmentHead'])->middleware('permission:branch setup create');
                Route::get('/search-division-head', [AdminController::class, 'searchDivisionHead'])->middleware('permission:branch setup create');
                Route::get('/search-unit-head', [AdminController::class, 'searchUnitHead'])->middleware('permission:branch setup create');
            
                // metadata
                Route::post('/create-metadata',[MetadataController::class, 'createMetaData'])->middleware('permission:metadata setup create');
                Route::post('/delete-metadata', [MetadataController::class, 'deleteMetaData'])->middleware('permission:metadata setup delete');
                Route::get('/metadatas', [MetadataController::class, 'getMetadata'])->middleware('permission:branch setup view');
                Route::get('/metadata-part', [MetadataController::class, 'getMetadata'])->middleware('permission:branch setup view');
                Route::post('/set-default-metadata', [MetadataController::class, 'setDefaultMetadata']);

                // document type
                Route::post('/create-document-type', [DocumentTypeController::class, 'createDocumentType'])->middleware('permission:document type create');
                Route::get('/setup-document-type', [DocumentTypeController::class, 'SetupDocumentType'])->middleware('permission:document type create');
                Route::post('/configure-meta-data', [DocumentTypeController::class, 'configureMetaData'])->middleware('permission:metadata setup update');
                Route::post('/duplicate-document-type', [DocumentTypeController::class, 'duplicateDocumentType'])->middleware('permission:document type update');
                Route::get('/delete-document-type', [DocumentTypeController::class, 'deleteDocumentType'])->middleware('permission:document type delete');

                //file type
                Route::get('/file-type', [AdminController::class, 'fileType'])->middleware('permission:branch setup create');
                Route::post('/file-type', [AdminController::class, 'createFileType'])->middleware('permission:branch setup create');
                Route::get('/get-file-type', [AdminController::class, 'getFileType'])->middleware('permission:branch setup create');
                Route::get('/delete-file-type', [AdminController::class, 'deleteFileType'])->middleware('permission:branch setup create');
                Route::post('/assign-file-type-to-department', [AdminController::class, 'assignFileTypesToBranch'])->middleware('permission:branch setup create');

                //manage users
                Route::get('/manage-users', [UserController::class, 'manageUsers'])->middleware('permission:manage users view');
                Route::post('/manage-users/toggle-status', [UserController::class, 'toggleUserStatus'])->middleware('permission:manage users update');
                Route::post('/manage-users/reset-pin', [UserController::class, 'resetApprovalPin'])->middleware('permission:manage users update');
                Route::post('/manage-users/reset-signature', [UserController::class, 'resetDigitalSignature'])->middleware('permission:manage users update');
                Route::post('/manage-users/send-password-reset', [UserController::class, 'sendPasswordReset'])->middleware('permission:manage users update');
                Route::get('/manage-users/export-csv', [UserController::class, 'exportUsersCsv'])->middleware('permission:manage users view');
                Route::get('/manage-users/dossier/{id}', [UserController::class, 'getOfficerDossier'])->middleware('permission:manage users view');
                Route::post('/verify-signature-pin', [UserController::class, 'verifySignaturePin']);
                Route::get('/add-user', [UserController::class, 'addUser'])->middleware('permission:manage users create');
                Route::get('/edit-user', [UserController::class, 'editUser'])->middleware('permission:manage users update');
                Route::post('/edit-user', [UserController::class, 'postEditUser'])->middleware('permission:manage users update');
                Route::get('/role-users', [UserController::class, 'RoleUsers']);
                Route::get('/delete-user/{id}', [UserController::class, 'deleteUser'])->middleware('permission:manage users delete');
                Route::post('/reject-account-request', [UserController::class, 'rejectAccountRequest'])->middleware('permission:manage users delete');

                //user registration
                Route::post('/user-registration', [RegistrationController::class, 'UsersRegistration']);

                //roles
                Route::get('/assign-role-to-super-admin', [AdminController::class, 'assignRoleToSuperAdmin']);
                Route::post('/add-permissions-to-role', [RoleController::class, 'addPermissionToRole']);
                Route::post('/add-permissions-to-department', [RoleController::class, 'addPermissionToDepartment']);
                Route::post('/add-permissions-to-user', [RoleController::class, 'addPermissionToUser'])->middleware('permission:access config update');
                Route::post('/create-permission', [RoleController::class, 'createCustomPermission'])->middleware('permission:access config update');

                // Dynamic Designations Management
                Route::get('/settings/designations', function() {
                    return redirect('/dashboard/governance-hub?tab=designations');
                })->middleware('permission:access config view');
                Route::get('/available-designations', [DesignationController::class, 'getAvailableDesignations']);
                Route::post('/settings/designations', [DesignationController::class, 'store'])->middleware('permission:access config update');
                Route::post('/settings/designations/{id}', [DesignationController::class, 'update'])->middleware('permission:access config update');
                Route::post('/settings/designations/{id}/toggle-status', [DesignationController::class, 'toggleStatus'])->middleware('permission:access config update');

                // System Governance & Organizational Structure Hub
                Route::get('/governance-hub', [GovernanceHubController::class, 'index'])->name('admin.governance-hub')->middleware('permission:access config view');
                Route::post('/governance-hub/onboard-department', [GovernanceHubController::class, 'onboardDepartment'])->middleware('permission:branch setup create');
                Route::post('/governance-hub/sync-branch-document-types', [GovernanceHubController::class, 'syncBranchDocumentTypes'])->middleware('permission:branch setup create');
                Route::post('/governance-hub/sync-branch-classifications', [GovernanceHubController::class, 'syncBranchClassifications'])->middleware('permission:branch setup create');
                Route::post('/governance-hub/sync-doctype-branches', [GovernanceHubController::class, 'syncDocumentTypeBranches'])->middleware('permission:document type create');
                Route::post('/governance-hub/sync-filetype-branches', [GovernanceHubController::class, 'syncFileTypeBranches'])->middleware('permission:branch setup create');
                Route::post('/governance-hub/create-file-type', [GovernanceHubController::class, 'createFileType'])->middleware('permission:branch setup create');
                Route::post('/governance-hub/delete-file-type', [GovernanceHubController::class, 'deleteFileType'])->middleware('permission:branch setup create');

                //Branches
                //Route::get('/branches', [ArchiveController::class, 'Branches']);
                Route::get('/branches/{branch}/{deptId}/{divId}/{unitId}', [ArchiveController::class, 'userBranches']);
                Route::get('/document-management', [ArchiveController::class, 'documentManagementIndex'])->middleware('permission:document management view');
                Route::get('/get-branches-by-role', [AdminController::class, 'getBranchesByRole']);

                //archiving /EDMS
                Route::post('/upload-files', [ArchiveController::class, 'uploadFiles'])->middleware('permission:edms create');
                Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles'])->middleware('permission:edms create');
                Route::get('/archiving/{id}/{folder_id}', [ArchiveController::class, 'Archiving'])->middleware('permission:edms create');
                Route::get('/create-folder',[ArchiveController::class, 'createFolder'])->middleware('permission:edms create');
                Route::post('/close-folder{id}', [ArchiveController::class, 'closeFolder'])->middleware('permission:edms create');
                Route::get('/get-division-with-unit', [ArchiveController::class, 'getDivisionWithUnit'])->middleware('permission:edms create');
                Route::get('/folder-view/{branch}/{folder_id}/{branch_id?}', [ArchiveController::class, 'folderView'])->name('folder-view2')->middleware('permission:edms view');

                //Mails
                Route::get('/mails', [MailController::class, 'mail'])->middleware('permission:mails view');
                Route::get('/mails/ReadMail/{emailId}', [MailController::class, 'readMail'])->middleware('permission:mails view');
                Route::get('/mails/DraftMail/{emailId}', [MailController::class, 'draftMail'])->middleware('permission:mails view');
                Route::post('/mails', [MailController::class, 'createMail'])->middleware('permission:mails create');
                Route::get('/mails/archiveMails', [MailController::class, 'getArchiveMails'])->middleware('permission:mails create');
                Route::get('/mails/SingleMail/{emailId}', [MailController::class, 'getSingleMail'])->middleware('permission:mails view');
                Route::get('/mails/SingleDraftMail/{emailId}', [MailController::class, 'getSingleDraftMail'])->middleware('permission:mails view');
                Route::get('/mails/starredMails', [MailController::class, 'getStarredMails'])->middleware('permission:mails view');
                Route::post('/mails/archive/{emailId}', [MailController::class, 'archiveMail'])->middleware('permission:mails create');
                Route::post('/mails/star/{emailId}', [MailController::class, 'starMail'])->middleware('permission:mails create');
                Route::post('/mails/drafts', [MailController::class, 'isDraft'])->middleware('permission:mails create');
                Route::get('/mails/drafts', [MailController::class, 'getDrafts'])->middleware('permission:mails create');
                Route::get('/mails/delete/{id}', [MailController::class, 'deleteMail'])->middleware('permission:mails delete');
                Route::post('/mails/reply-email', [MailController::class, 'sendReply'])->middleware('permission:mails create');
                Route::get('/get-all-mails-folder/{folder}', [MailController::class, 'getAllMailsFolders']);
                Route::post('/mails/forward-to-agf/{emailId}', [MailController::class, 'forwardToAgf']);

                //workflow
                Route::get('/workflow', [WorkFlowController::class, 'index'])->middleware('permission:workflow view');
                Route::post('/workflow', [WorkFlowController::class, 'createWorkFlow'])->middleware('permission:workflow create');
                Route::get('/search-workflow', [WorkFlowController::class, 'searchWorkflow'])->middleware('permission:workflow view');

                //workflow settings
                Route::get('/workflow-settings', [WorkflowSettingsController::class, 'index'])->middleware('permission:workflow view');
                Route::post('/workflow-settings', [WorkflowSettingsController::class, 'store'])->middleware('permission:workflow create');
                Route::post('/workflow-settings/{id}', [WorkflowSettingsController::class, 'update'])->middleware('permission:workflow create');
                Route::get('/delete-workflow-settings/{id}', [WorkflowSettingsController::class, 'destroy'])->middleware('permission:workflow create');

                //workflow builder studio & management
                Route::get('/workflow-builder', [WorkflowBuilderController::class, 'index'])->name('admin.workflow-builder.index')->middleware('permission:workflow view');
                Route::get('/workflow-builder/studio/{versionId?}', [WorkflowBuilderController::class, 'studio'])->name('admin.workflow-builder.studio')->middleware('permission:workflow create');
                Route::post('/workflow-builder/studio/save', [WorkflowBuilderController::class, 'saveStudioWorkflow'])->name('admin.workflow-builder.studio.save')->middleware('permission:workflow create');
                Route::post('/workflow-builder', [WorkflowBuilderController::class, 'store'])->name('admin.workflow-builder.store')->middleware('permission:workflow create');
                Route::post('/workflow-builder/{id}/toggle-status', [WorkflowBuilderController::class, 'toggleStatus'])->name('admin.workflow-builder.toggleStatus')->middleware('permission:workflow create');
                Route::delete('/workflow-builder/{id}', [WorkflowBuilderController::class, 'destroy'])->name('admin.workflow-builder.destroy')->middleware('permission:workflow create');
                Route::get('/workflow-builder/version/{versionId}', [WorkflowBuilderController::class, 'show'])->name('admin.workflow-builder.show')->middleware('permission:workflow view');
                Route::post('/workflow-builder/version/{versionId}/steps', [WorkflowBuilderController::class, 'storeStep'])->name('admin.workflow-builder.storeStep')->middleware('permission:workflow create');
                Route::post('/workflow-builder/version/{versionId}/transitions', [WorkflowBuilderController::class, 'storeTransition'])->name('admin.workflow-builder.storeTransition')->middleware('permission:workflow create');
                Route::post('/workflow-builder/version/{versionId}/publish', [WorkflowBuilderController::class, 'publish'])->name('admin.workflow-builder.publish')->middleware('permission:workflow create');
                Route::post('/workflow-builder/version/{versionId}/approve', [WorkflowBuilderController::class, 'approveVersion'])->name('admin.workflow-builder.approveVersion')->middleware('permission:workflow create');
                Route::post('/workflow-builder/version/{versionId}/rollback', [WorkflowBuilderController::class, 'rollbackVersion'])->name('admin.workflow-builder.rollbackVersion')->middleware('permission:workflow create');
                Route::post('/workflow-builder/version/{versionId}/settings', [WorkflowBuilderController::class, 'updateSettings'])->name('admin.workflow-builder.updateSettings')->middleware('permission:workflow create');
                Route::post('/workflow-builder/version/{versionId}/clone', [WorkflowBuilderController::class, 'cloneVersion'])->name('admin.workflow-builder.cloneVersion')->middleware('permission:workflow create');
                
                Route::post('/workflow-builder/step/{stepId}/sla', [WorkflowBuilderController::class, 'storeSlaConfiguration'])->name('admin.workflow-builder.storeSlaConfiguration')->middleware('permission:workflow create');
                Route::post('/workflow-builder/step/{stepId}/checklist', [WorkflowBuilderController::class, 'storeChecklist'])->name('admin.workflow-builder.storeChecklist')->middleware('permission:workflow create');
                Route::post('/workflow-builder/step/{stepId}/form', [WorkflowBuilderController::class, 'storeFormSchema'])->name('admin.workflow-builder.storeFormSchema')->middleware('permission:workflow create');
                Route::post('/workflow-builder/transition/{transitionId}/condition', [WorkflowBuilderController::class, 'storeCondition'])->name('admin.workflow-builder.storeCondition')->middleware('permission:workflow create');

                //Working Calendar
                Route::get('/working-calendar', [WorkingCalendarController::class, 'index']);
                Route::post('/working-calendar/days', [WorkingCalendarController::class, 'updateWorkingDays']);
                Route::post('/working-calendar/holidays', [WorkingCalendarController::class, 'storeHoliday']);
                Route::delete('/working-calendar/holidays/{id}', [WorkingCalendarController::class, 'deleteHoliday']);

                //Document Templates
                Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
                Route::get('/templates/available', [TemplateController::class, 'getAvailable'])->name('templates.available');
                Route::get('/templates/{id}/hydrate', [TemplateController::class, 'hydrate'])->name('templates.hydrate');
                Route::get('/document-templates/get', [DocumentGenerationController::class, 'getTemplates'])->name('document.templates.get');
                Route::post('/document-templates/generate-file', [DocumentGenerationController::class, 'generateStandaloneFile'])->name('document.templates.generate-file');
                Route::post('/document-templates/preview-html', [DocumentGenerationController::class, 'previewHtml'])->name('document.templates.preview-html');
                Route::post('/task/{task}/document/generate', [DocumentGenerationController::class, 'generate'])->name('task.document.generate');
                Route::post('/templates/convert-template-file', [DepartmentHubController::class, 'convertTemplateFile'])->name('templates.convert-template-file');
                Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
                Route::put('/templates/{id}', [TemplateController::class, 'update'])->name('templates.update');
                Route::delete('/templates/{id}', [TemplateController::class, 'destroy'])->name('templates.destroy');



                //task
                Route::post('/create-task', [TaskController::class, 'createTask'])->middleware('permission:workflow create');
                Route::post('/task-append-signature', [TaskController::class, 'appendSignature']);
                Route::post('/return-task', [TaskController::class, 'returnTask']);
                Route::post('/approve-task', [TaskController::class, 'approveTask']);
                Route::get('/task-by-status', [TaskController::class, 'getTaskByStatus'])->middleware('permission:workflow view');
                Route::any('/view-task/{id}', [TaskController::class, 'viewTask'])->middleware('permission:workflow view');
                Route::get('/download-task-file/{file_name}', [TaskController::class, 'downloadFile'])->middleware('permission:workflow view');
                Route::post('/task-comment', [TaskController::class, 'taskComment']);
                Route::post('/upload-task-document', [TaskController::class, 'uploadTaskDocument']);
                Route::get('/remove-task-image', [TaskController::class, 'removeTaskImage'])->middleware('permission:workflow update');
                Route::post('/assign-task', [TaskController::class, 'AssignTask']);
                Route::post('/task-submission', [TaskController::class, 'taskSubmission']);
                Route::post('/task-save-modified-pdf', [TaskController::class, 'saveModifiedPdf']);
                Route::post('/mark-as-completed', [TaskController::class, 'markAsComplete']);
                Route::post('/request-revision', [TaskController::class, 'requestRevision']);
                Route::post('/re-open-task', [TaskController::class, 'reopenTask']);
                Route::get('/get-department-kra', [TaskController::class, 'getDepartmentKRA']);
                Route::post('/forward-task', [TaskController::class, 'forwardTask']);
                Route::post('/request-information', [TaskController::class, 'requestInformation']);
                Route::post('/provide-information', [TaskController::class, 'provideInformation']);
                Route::post('/resume-task', [TaskController::class, 'resumeTask']);
                Route::post('/batch-assign-tasks', [TaskController::class, 'batchAssignTasks']);
                Route::post('/batch-archive-tasks', [TaskController::class, 'batchArchiveTasks']);
                Route::get('/task-triage-details/{id}', [TaskController::class, 'getTaskTriageDetails']);
                Route::get('/get-task-discussions/{id}', [TaskController::class, 'getTaskDiscussions']);
                Route::get('/available-workflows', [TaskController::class, 'getAvailableWorkflows']);
                Route::post('/executive-route-task', [TaskController::class, 'executiveRouteTask'])->name('task.executive-route');


                // Streamlined Submission Workflow Routes
                Route::get('/tasks/{task}/chain-info', [StreamlinedTaskWorkflowController::class, 'getChainInfo']);
                Route::post('/tasks/{task}/sign-forward', [StreamlinedTaskWorkflowController::class, 'signAndForward']);
                Route::post('/tasks/{task}/return', [StreamlinedTaskWorkflowController::class, 'returnTask']);
                Route::post('/tasks/{task}/submit-for-review', [StreamlinedTaskWorkflowController::class, 'submitForReview']);
                Route::post('/tasks/{task}/approve-submission', [StreamlinedTaskWorkflowController::class, 'approveSubmission']);
                Route::post('/tasks/{task}/upload-attachment', [StreamlinedTaskWorkflowController::class, 'uploadAttachment']);
                Route::delete('/tasks/{task}/attachments/{attachment}', [StreamlinedTaskWorkflowController::class, 'deleteAttachment']);
                Route::get('/tasks/{task}/versions', [StreamlinedTaskWorkflowController::class, 'getVersionHistory']);
                Route::post('/task-annotations/{annotation}/resolve', [StreamlinedTaskWorkflowController::class, 'resolveAnnotation']);
                Route::get('/tasks-awaiting-my-action', [StreamlinedTaskWorkflowController::class, 'awaitingMyActionQueue']);

                // Generic Dynamic Workflow Runtime Routes
                Route::prefix('generic-workflow')->group(function () {
                    Route::get('/state/{taskId}', [GenericWorkflowInstanceController::class, 'getState']);
                    Route::get('/tasks/{taskId}/state', [GenericWorkflowInstanceController::class, 'getState']);
                    Route::get('/tasks/{taskId}/actions', [GenericWorkflowInstanceController::class, 'getAvailableActions']);
                    Route::get('/instances/{taskId}/actions', [GenericWorkflowInstanceController::class, 'getAvailableActions']);
                    Route::post('/tasks/{taskId}/execute', [GenericWorkflowInstanceController::class, 'executeAction']);
                    Route::post('/instances/{taskId}/execute', [GenericWorkflowInstanceController::class, 'executeAction']);
                    Route::post('/execute-action', function(\Illuminate\Http\Request $request) {
                        $taskId = $request->input('task_id');
                        return app(\App\Http\Controllers\GenericWorkflowInstanceController::class)->executeAction($request, $taskId);
                    });
                    Route::post('/tasks/{taskId}/pause', [GenericWorkflowInstanceController::class, 'pauseTask']);
                    Route::post('/tasks/{taskId}/resume', [GenericWorkflowInstanceController::class, 'resumeTask']);
                    Route::post('/tasks/{taskId}/start-sub-workflow', [GenericWorkflowInstanceController::class, 'startSubWorkflow']);
                });

                //Ratings
                Route::post('/rate-user', [RatingsController::class, 'rateUser']);

                //users
                Route::get('/search-users', [UserController::class, 'searchUsers']);

                //chat
                // Route::get('/chat', [ChatController::class, 'index'])->middleware('permission:chat view');
                // Route::post('/chat', [ChatController::class, 'chat'])->middleware('permission:chat create');

                Route::get('/chat', [ChatController::class, 'chat'])->name('chat')->middleware('permission:chat view');
                Route::post('/chat', [ChatController::class, 'sendMessageToUser'])->name('chat.send')->middleware('permission:chat view');
                Route::get('/chat/users', [ChatController::class, 'getAllUsers'])->middleware('permission:chat view');
                Route::get('/chat/usersConversation/{userId}', [ChatController::class, 'getConversationWithUser'])->middleware('permission:chat view');
                Route::post('/chat/group/send-message', [ChatController::class, 'sendMessageToGroup'])->middleware('permission:chat view');
                Route::get('/chat/group/{groupId}/{groupType}', [ChatController::class, 'getGroupMessages'])->middleware('permission:chat view');
                Route::post('/chat/groups', [ChatController::class, 'createGroup'])->middleware('permission:chat view');
                Route::post('/chat/users/messages', [ChatController::class, 'sendMessageToUser'])->middleware('permission:chat view');
                Route::post('/chat/users/block', [ChatController::class, 'blockUser'])->middleware('permission:chat view');
                Route::post('/chat/users/unblock', [ChatController::class, 'unblockUser'])->middleware('permission:chat view');
                Route::post('/chat/users/archive', [ChatController::class, 'archiveChat'])->middleware('permission:chat view');
                Route::post('/chat/users/unarchive', [ChatController::class, 'unarchiveChat'])->middleware('permission:chat view');
                Route::get('/chat/users/archived', [ChatController::class, 'getArchivedContacts'])->middleware('permission:chat view');
                Route::get('/chat/groups/{category}', [ChatController::class, 'getGroupsByCategory'])->middleware('permission:chat view');
                Route::get('/chat/categories', [ChatController::class, 'getAllCategories'])->middleware('permission:chat view');
                Route::get('/chat/groups/{category}/{groupId}', [ChatController::class, 'getGroupWithConversations'])->middleware('permission:chat view');
                Route::get('/chat/all-groups', [ChatController::class, 'getUserGroups'])->middleware('permission:chat view');
                Route::delete('/chat/messages/{id}', [ChatController::class, 'deleteMessage'])->middleware('permission:chat view');
                Route::post('/chat/messages/{id}/pin', [ChatController::class, 'togglePinMessage'])->middleware('permission:chat view');
                Route::post('/chat/messages/{id}/star', [ChatController::class, 'toggleStarMessage'])->middleware('permission:chat view');
                Route::post('/chat/messages/{id}/react', [ChatController::class, 'reactToMessage'])->middleware('permission:chat view');
                Route::post('/chat/messages/forward', [ChatController::class, 'forwardMessage'])->middleware('permission:chat view');
                Route::put('/chat/messages/{id}', [ChatController::class, 'editMessage'])->middleware('permission:chat view');
                Route::get('/chat/edms-documents', [ChatController::class, 'searchEdmsDocuments'])->middleware('permission:chat view');
                Route::post('/chat/create-task', [ChatController::class, 'createTaskFromChatMessage'])->middleware('permission:chat view');
                Route::get('/chat/export', [ChatController::class, 'exportConversation'])->middleware('permission:chat view');
                Route::get('/chat/global-search', [ChatController::class, 'globalSearch'])->middleware('permission:chat view');
                Route::get('/chat/archive-status', [ChatController::class, 'checkArchiveStatus'])->middleware('permission:chat view');

                // Personal Messages (Chat Requests)
                Route::get('/chat/search-personal', [ChatController::class, 'searchUsersForChat'])->middleware('permission:chat view');
                Route::post('/chat/request', [ChatController::class, 'sendChatRequest'])->middleware('permission:chat view');
                Route::post('/chat/request/{id}/accept', [ChatController::class, 'acceptChatRequest'])->middleware('permission:chat view');
                Route::post('/chat/request/{id}/decline', [ChatController::class, 'declineChatRequest'])->middleware('permission:chat view');
                Route::get('/chat/requests', [ChatController::class, 'getChatRequests'])->middleware('permission:chat view');
                Route::get('/chat/personal-contacts', [ChatController::class, 'getPersonalContacts'])->middleware('permission:chat view');
                Route::post('/chat/heartbeat', [ChatController::class, 'heartbeat'])->middleware('permission:chat view');
                Route::post('/chat/leave', [ChatController::class, 'leaveChat'])->middleware('permission:chat view');

                //Access level or Roles
                Route::get('/access-level', function() {
                    return redirect('/dashboard/governance-hub?tab=access');
                })->middleware('permission:access config view');
                Route::get('/access-control', function() {
                    return redirect('/dashboard/governance-hub?tab=access');
                })->middleware('permission:access config view');
                Route::post('/change-accessLevel-status', [RoleController::class, 'toggleStatus'])->middleware('permission:access config update');
                Route::post('/create-role', [RoleController::class, 'createRole'])->middleware('permission:access config update');
                Route::get('/delete-level', [RoleController::class, 'deleteAccessLevel'])->middleware('permission:access config update');

                //Correspondence (Integrated into Registry)
                Route::get('/correspondence', function() {
                    return redirect('/dashboard/registry?tab=correspondence');
                });
                Route::post('/correspondence', [CorrespondenceController::class, 'createCorrespondence']);
                Route::get('/correspondence/view-files/{id}', [CorrespondenceController::class, 'viewFile'])->middleware('permission:registry view|edms view|workflow view');
                Route::get('/correspondence/departments', [CorrespondenceController::class, 'getDepartments']);

                //support
                Route::get('/support', function() {
                    return redirect('/dashboard/support');
                })->middleware('permission:support view');
           // });
        

              //REGISTRY
                Route::get('/agf', [DashboardController::class, 'agfDashboard'])->middleware('role_or_permission:AGF Registry|agf registry|agf registry view|super admin')->name('agf.dashboard');
                Route::get('/agf-registry', [RegistryController::class, 'adminRegistry'])->middleware('role_or_permission:AGF Registry|agf registry|agf registry view|admin registry view|super admin');
                Route::get('/admin-registry', [RegistryController::class, 'adminRegistry'])->middleware('role_or_permission:AGF Registry|agf registry|agf registry view|admin registry view|super admin');
                // Route::get('/registry/entry/login', [RegistryController::class, 'login'])->middleware('permission:registry view');
                Route::get('/registry', [RegistryController::class, 'registry'])->middleware('permission:registry view')->name('registry');
                Route::get('/registry/correspondences', [RegistryController::class, 'getCorrespondences'])->middleware('permission:registry view');
                Route::get('/agf-tasks', [RegistryController::class, 'agfTasks']);
                Route::get('/agf-track-tasks', [\App\Http\Controllers\AGFTrackTasksController::class, 'index']);
                Route::get('/departments', [RegistryController::class, 'getDepartments']);
                Route::get('/dept', [RegistryController::class, 'getDept']);
                Route::get('/branch-users', [RegistryController::class, 'getBranchUsers']);
                Route::get('/workflows', [RegistryController::class, 'getWorkflows'])->middleware('permission:registry view|agf registry view|workflow view');
                Route::get('/registry/dept', [RegistryController::class, 'getDepartment']);
                // Route::get('/registry/deptFiles/{deptId}', [RegistryController::class, 'getFilesByDept']);
                Route::get('/registry/branchFiles', [RegistryController::class, 'getBranchFiles'])->middleware('permission:registry view');
                Route::post('/registry/newfile', [RegistryController::class, 'createFile']);
                Route::put('/registry/editfile/{id}', [RegistryController::class, 'editFile']);
                Route::post('/registry/newdepartment', [RegistryController::class, 'createDepartment']);
                Route::post('/registry/updateFileStatus/{fileId}', [RegistryController::class, 'updateFileStatus']);
                // Route::post('/registry/forward/{fileId}', [RegistryController::class, 'forwardFile']);
                Route::post('/registry/registerUser', [UserController::class, 'registerUser']);
                Route::post('/registry/loginUser', [UserController::class, 'loginUser']);
                Route::post('/registry/transfer/{filedept}', [RegistryController::class, 'transferFile']);

                // Route::post('/registry/createDivision/{deptId}', [RegistryController::class, 'createDivision']);
                // Route::post('/registry/createUnit/{divisonId}', [RegistryController::class, 'createUnit']);
                Route::get('/registry/getDivisionsInDepartment/{deptId}', [RegistryController::class, 'getDivisionsInDepartment']);
                Route::get('/registry/getUnitsInDivision/{divisionId}', [RegistryController::class, 'getUnitsInDivision']);
                Route::post('/registry/sendToDivision', [RegistryController::class, 'sendToDivision']);
                Route::post('/registry/moveToUnit', [RegistryController::class, 'moveToUnit']);
                Route::get('/registry/getFilesInDivision/{divisionId}', [RegistryController::class, 'getFilesInDivision']);
                Route::get('/registry/getFilesInUnit/{unitId}', [RegistryController::class, 'getFilesInUnit']);
                Route::get('/registry/getFileMovements/{fileId}', [RegistryController::class, 'getFileMovements']);
                Route::get('/registry/files/department/{departmentId}/name/{name}', [RegistryController::class, 'getFileByName'])->where('name', '.*');

                Route::get('/registry/user-branches', [RegistryController::class, 'getUserBranches']);
                Route::get('/registry/track-files', [RegistryController::class, 'TrackFile']);
                Route::get('/registry/tracked', [RegistryController::class, 'getTrackedFile']);

                //Admin Registry
                Route::get('/admin-registry-branch', [RegistryController::class, 'getAdminRegistryBranch']);

                //PMS
                Route::get('/pms', [PMSController::class, 'index'])->middleware('permission:pms view');
                Route::get('/pms/overview', [PMSController::class, 'overview'])->middleware('permission:pms view');
                Route::get('/pms/settings', [PMSController::class, 'settings'])->middleware('permission:pms view');
                Route::get('/get-department-users', [PMSController::class, 'getDepartmentUsers'])->middleware('permission:pms view');
                Route::get('/pms-user-profile/{id}', [PMSController::class, 'viewPMSUserProfile'])->middleware('permission:pms view');
                Route::get('/pms-current-user-profile', [PMSController::class, 'viewPMSCurrentUserProfile'])->middleware('permission:pms view');
                Route::get('/pms-attendance/{id}', [PMSController::class, 'getAttendance'])->middleware('permission:pms view');
                Route::get('/pms/report/user/{id}', [PMSController::class, 'generateUserReport'])->name('pms.report.user');
                Route::get('/pms/report/department/{id?}', [PMSController::class, 'generateDepartmentReport'])->name('pms.report.department');
                Route::get('/pms/report/attendance/{id}', [PMSController::class, 'generateAttendanceReport'])->name('pms.report.attendance');
                Route::get('/pms/attendance/status', [PMSController::class, 'getAttendanceStatus'])->name('pms.attendance.status');
                Route::post('/pms/attendance/check-in', [PMSController::class, 'checkInAttendance'])->name('pms.attendance.checkin');
                Route::post('/pms/attendance/check-out', [PMSController::class, 'checkOutAttendance'])->name('pms.attendance.checkout');

                //KRA
                Route::post('/create-kra', [KRAController::class, 'createKRA'])->middleware('permission:pms create|permission:pms update');
                Route::post('/update-kra', [KRAController::class, 'updateKRA'])->middleware('permission:pms update');
                Route::get('/get-kra', [KRAController::class, 'getKRA'])->middleware('permission:pms view');
                Route::post('/add-kpi', [KRAController::class, 'addKPI'])->middleware('permission:pms create|permission:pms update');
                Route::get('/delete-kpi', [KRAController::class, 'deleteKPI'])->middleware('permission:pms delete');
                Route::get('/delete-kra', [KRAController::class, 'deleteKRA'])->middleware('permission:pms delete');

                //KPI
                Route::post('/add-certificate', [PMSController::class, 'saveCertificate']);
                Route::get('/delete-certificate', [PMSController::class, 'deleteCertificate']);

                 //SUPPORT
                Route::get('/support/', [SupportController::class, 'support'])->name('support')->middleware('permission:support view');
                Route::post('/support/support-details', [SupportController::class, 'sendSupportDetails'])->middleware('permission:support update');
                Route::get('/support/support-details', [SupportController::class, 'getSupportDetails'])->middleware('permission:support view');
                Route::post('/support/faqs', [SupportController::class, 'SaveFaqs'])->middleware('permission:support update');
                Route::post('/support/faqs/{id}/update', [SupportController::class, 'updateFaq'])->middleware('permission:support update');
                Route::delete('/support/faqs/{id}', [SupportController::class, 'deleteFaq'])->middleware('permission:support delete');
                Route::get('/support/get-faqs', [SupportController::class, 'getFaqs'])->middleware('permission:support view');
                Route::post('/support/doc', [SupportController::class, 'saveDocumentation'])->middleware('permission:support update');
                Route::post('/support/doc/{id}/update', [SupportController::class, 'updateDoc'])->middleware('permission:support update');
                Route::delete('/support/doc/{id}', [SupportController::class, 'deleteDoc'])->middleware('permission:support delete');
                Route::get('/support/get-docs', [SupportController::class, 'getDocumentations'])->middleware('permission:support view');
                Route::post('/support/tickets', [SupportController::class, 'storeTicket'])->middleware('permission:support create');
                Route::post('/support/tickets/{id}/status', [SupportController::class, 'updateTicketStatus'])->middleware('permission:support dispatch');

                //ANALYTICS
                Route::get('/analytics', [AnalyticsController::class, 'analytics']);
                Route::get('/analytics/server-status', [AnalyticsController::class, 'getServerStatus']);
                Route::get('/analytics/user-logins', [AnalyticsController::class, 'getUserLogins']);
                Route::get('/analytics/agf-summary', [AnalyticsController::class, 'getAGFSummary']);
                Route::get('/analytics/director-summary', [AnalyticsController::class, 'getDirectorSummary']);
                Route::get('/analytics/division-summary', [AnalyticsController::class, 'getDivisionSummary']);
                Route::get('/analytics/unit-summary', [AnalyticsController::class, 'getUnitSummary']);
                Route::get('/analytics/roles', [RoleController::class, 'getRoles']);

                //Leave & Official Assignment
                Route::post('/add-leave', [UserController::class, 'addLeave']);
                Route::post('/apply-leave', [UserController::class, 'applyLeave']);
                Route::post('/approve-leave', [UserController::class, 'approveLeave']);
                Route::post('/reject-leave', [UserController::class, 'rejectLeave']);
                Route::get('/cancel-leave', [UserController::class, 'cancelLeave']);
                Route::get('/get-user-leaves', [UserController::class, 'getUserLeaves']);
                Route::get('/get-pending-leaves', [UserController::class, 'getPendingLeaves']);
                Route::get('/get-leave-relief-officers', [UserController::class, 'getLeaveReliefOfficers']);
                Route::get('/get-all-users', [UserController::class, 'getAllUsers']);

                //Misconduct
                Route::post('/report-misconduct', [UserController::class, 'misConduct']);
                Route::get('/get-user-misconducts', [UserController::class, 'getUserMisconducts']);

                // Disciplinary Queries (Superior <-> Subordinate)
                Route::get('/queries', [StaffQueryController::class, 'index'])->middleware('permission:queries view');
                Route::post('/queries', [StaffQueryController::class, 'store'])->middleware('permission:queries create');
                Route::get('/queries/subordinates', [StaffQueryController::class, 'getSubordinates'])->middleware('permission:queries view');
                Route::post('/queries/{id}/respond', [StaffQueryController::class, 'respond'])->middleware('permission:queries respond');
                Route::post('/queries/{id}/verdict', [StaffQueryController::class, 'verdict'])->middleware('permission:queries verdict');
                Route::get('/queries/user/{userId}', [StaffQueryController::class, 'getUserQueries'])->middleware('permission:queries view');

                // SERVICOM Grievance & Whistleblower Portal
                Route::get('/servicom', [ServicomController::class, 'index'])->middleware('permission:servicom view');
                Route::post('/servicom/report', [ServicomController::class, 'storeReport']);
                Route::post('/servicom/status/{id}', [ServicomController::class, 'updateStatus']);
                Route::post('/servicom/panel/{id}', [ServicomController::class, 'constitutePanel']);
                Route::post('/servicom/panel/{panelId}/findings', [ServicomController::class, 'updatePanelFindings']);

                //Audit log
                Route::get('/audit-log', [AuditController::class, 'index'])->middleware('permission:audit view');
                Route::get('/audit-log/export', [AuditController::class, 'exportCsv'])->middleware('permission:audit export|audit view');
                Route::get('/audit-log/verify-integrity', [AuditController::class, 'verifyIntegrity'])->middleware('permission:audit verify|audit view');
                Route::get('/audit-logs', function() { return redirect('/dashboard/audit-log'); });
                Route::get('/audit-logs/export', function() { return redirect('/dashboard/audit-log/export'); });
                Route::get('/audit-logs/verify-integrity', function() { return redirect('/dashboard/audit-log/verify-integrity'); });

                // Navigation & Module Aliases
                Route::get('/settings', function() { return redirect('/settings'); });
                Route::get('/procurement', function() { return redirect('/dashboard/procurements'); });
                Route::get('/administration/queries', function() { return redirect('/dashboard/queries'); });

                //Recycle bin
                Route::get('/recycle-bin', [RecycleBinController::class, 'index'])->middleware('permission:recycle bin view');
                Route::post('/recycle-bin-restore', [RecycleBinController::class, 'restoreRecycleBin'])->middleware('permission:recycle bin update');
                Route::get('/recycle-bin-delete', [RecycleBinController::class, 'deleteRecyleBinFile'])->middleware('permission:recycle bin delete');
                Route::post('/recycle-bin/batch-restore', [RecycleBinController::class, 'batchRestore'])->middleware('permission:recycle bin update');
                Route::post('/recycle-bin/batch-delete', [RecycleBinController::class, 'batchDelete'])->middleware('permission:recycle bin delete');
                Route::post('/recycle-bin/empty', [RecycleBinController::class, 'emptyBin'])->middleware('permission:recycle bin delete');

                //Shared files
                Route::get('/shared-files', [ArchiveController::class, 'sharedFiles'])->middleware('permission:shared files view');

                Route::post('/update-profile-image', [UserController::class, 'updateProfileImage']);

                // Statutory Procurement & BPP 2007 Operations
                Route::get('/procurements', [ProcurementController::class, 'index']);
                Route::post('/procurement/create', [ProcurementController::class, 'store']);
                Route::post('/procurement/{id}/evaluate', [ProcurementController::class, 'evaluateBpp']);
                Route::post('/procurement/{id}/generate-lpo', [ProcurementController::class, 'generateLpo']);
                Route::post('/procurement/{id}/dispatch-to-finance', [ProcurementController::class, 'dispatchToFinance']);
                Route::get('/procurement/{id}/print-lpo', [ProcurementController::class, 'printLpo']);

                // Statutory Administration & PSR 2021 Operations
                Route::get('/administration/nominal-roll', [AdministrationController::class, 'nominalRoll']);
                Route::post('/administration/nominal-roll/{id}/verify', [AdministrationController::class, 'verifyNominalRoll']);
                Route::get('/administration/leaves', [AdministrationController::class, 'leaves']);
                Route::post('/administration/leaves/{id}/process', [AdministrationController::class, 'processLeave']);
                Route::get('/administration/queries', [AdministrationController::class, 'queries']);
                Route::post('/administration/queries/issue', [AdministrationController::class, 'issuePsrQuery']);
                Route::post('/administration/queries/{id}/resolve', [AdministrationController::class, 'resolvePsrQuery']);

                // National Federal Accountants Pool Management (Strictly Dept 14 DA + AD Pool)
                Route::get('/administration/pool', [PoolManagementController::class, 'index']);
                Route::post('/administration/pool/officers', [PoolManagementController::class, 'storeOfficer']);
                Route::get('/administration/pool/requisitions', [PoolManagementController::class, 'requisitions']);
                Route::post('/administration/pool/requisitions', [PoolManagementController::class, 'storeRequisition']);
                Route::get('/administration/pool/requisitions/{id}/match', [PoolManagementController::class, 'matchCandidates']);
                Route::post('/administration/pool/postings/issue', [PoolManagementController::class, 'issuePosting']);
                Route::post('/administration/pool/postings/{id}/assume', [PoolManagementController::class, 'recordAssumption']);
                Route::get('/administration/pool/postings', [PoolManagementController::class, 'postings']);

                // Administration Department SOP Desks (Confirmations, APER, Welfare, Sub-Registries, Stamps)
                Route::get('/administration/confirmations', [AdministrationDepartmentController::class, 'confirmations']);
                Route::post('/administration/confirmations/{id}/recommend', [AdministrationDepartmentController::class, 'recommendConfirmation']);
                Route::get('/administration/aper', [AdministrationDepartmentController::class, 'aperSubmissions']);
                Route::post('/administration/aper/{id}/evaluate', [AdministrationDepartmentController::class, 'evaluateAper']);
                Route::get('/administration/welfare', [AdministrationDepartmentController::class, 'welfareClaims']);
                Route::post('/administration/welfare', [AdministrationDepartmentController::class, 'storeWelfareClaim']);
                Route::post('/administration/welfare/{id}/process', [AdministrationDepartmentController::class, 'processWelfareClaim']);
                Route::get('/administration/movement-cards', [AdministrationDepartmentController::class, 'fileMovementCards']);
                Route::post('/administration/movement-cards', [AdministrationDepartmentController::class, 'storeFileMovement']);
                Route::post('/administration/movement-cards/{id}/treat', [AdministrationDepartmentController::class, 'markFileTreated']);
                Route::get('/administration/stamps', [AdministrationDepartmentController::class, 'getDigitalStamps']);
                Route::get('/administration/stamps/preview', [AdministrationDepartmentController::class, 'previewSvgStamp']);
                Route::post('/administration/stamps', [AdministrationDepartmentController::class, 'storeDigitalStamp']);

                // Departmental & Directorate Configuration & Governance Hub
                Route::get('/directorates', [DepartmentHubController::class, 'index'])->name('admin.department-hub');
                Route::post('/directorates/toggle-capability', [DepartmentHubController::class, 'toggleCapability'])->name('admin.department-hub.toggle-capability');
                Route::post('/directorates/assign-template', [DepartmentHubController::class, 'assignTemplate'])->name('admin.department-hub.assign-template');
                Route::post('/directorates/convert-template-file', [DepartmentHubController::class, 'convertTemplateFile'])->name('admin.department-hub.convert-template-file');
                Route::post('/directorates/create-template', [DepartmentHubController::class, 'createTemplate'])->name('admin.department-hub.create-template');
                Route::delete('/directorates/templates/{id}', [DepartmentHubController::class, 'deleteTemplate'])->name('admin.department-hub.delete-template');
                Route::get('/directorates/oversight', [DepartmentHubController::class, 'oversight'])->name('admin.directorate-oversight');
                Route::post('/directorates/call-up-file', [DepartmentHubController::class, 'callUpFile'])->name('admin.directorate-oversight.call-up');
            
        });

      
   

    //Roles
    Route::get('/create-roles', [RoleController::class, 'createRoles']);

    //permissions
    Route::get('/create-permissions', [PermissionController::class, 'createPermission']);

    //email
    Route::get('/auth-email-exist', [RegistrationController::class, 'checkMailExit']);
    //phone
    Route::get('/auth-phone-exist', [RegistrationController::class, 'checkPhoneExist']);

    //roles
    Route::get('/assign-role-to-super-admin', [AdminController::class, 'assignRoleToSuperAdmin']);

    //logout
    Route::get('/log-out', [LoginController::class, 'logout']);

    //search
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/delete-from-recent-search', [SearchController::class, 'deleteFromRecentSearch']);

    //Document type
    Route::get('/get-document-type', [DocumentTypeController::class, 'getDocumentType']);
    Route::get('/get-branch-document-type', [DocumentTypeController::class, 'getBranchDocumentType']);

    //archiving
    Route::get('/folder-search', [ArchiveController::class, 'folderSearch']);

    //notifications 
    Route::get('/dashboard/notifications', [UserController::class, 'notificationsHub'])->middleware('permission:notifications view');
    Route::get('/notifications', [UserController::class, 'notificationsHub'])->middleware('permission:notifications view');
    Route::get('/user-notifications', [UserController::class, 'getUserUnreadNotification'])->middleware('permission:notifications view');
    Route::post('/mark-notification-as-read', [UserController::class, 'markNotificationAsRead'])->middleware('permission:notifications manage|notifications view');
    Route::post('/mark-notification-as-unread', [UserController::class, 'markNotificationAsUnread'])->middleware('permission:notifications manage|notifications view');
    Route::post('/delete-notification', [UserController::class, 'deleteNotification'])->middleware('permission:notifications manage|notifications view');

     //settings - direct submodule views and navigation
     Route::get('/settings', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/profile', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/organisation', [SettingsController::class, 'settings'])->middleware('permission:settings organisation');
     Route::get('/settings/security', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/security-recovery', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/preferences', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/notifications', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/notifications-config', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/localisation', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/settings/integrations', [SettingsController::class, 'settings'])->middleware('permission:settings integrations');
     Route::get('/settings/development', [SettingsController::class, 'settings'])->middleware('permission:settings system');
     Route::get('/settings/development-team', [SettingsController::class, 'settings'])->middleware('permission:settings system');
     Route::get('/settings/system-configurations', [SettingsController::class, 'settings'])->middleware('permission:settings system');
     Route::get('/profile', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/preferences', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/security', [SettingsController::class, 'settings'])->middleware('permission:settings view');
     Route::get('/localisation', [SettingsController::class, 'settings'])->middleware('permission:settings view');

     //settings actions & APIs
     Route::post('/settings/organisation', [SettingsController::class, 'saveOrganisation'])->middleware('permission:settings organisation');
     Route::get('/settings/get-organisation', [SettingsController::class, 'getOrganisation'])->middleware('permission:settings view');
     Route::post('/settings/preferences', [SettingsController::class, 'savePreferences'])->middleware('permission:settings view');
     Route::post('/settings/update-preferences', [SettingsController::class, 'updatePreferences'])->middleware('permission:settings view');
     Route::post('/settings/notifications', [SettingsController::class, 'saveNotificationSettings'])->middleware('permission:settings view');
     Route::post('/settings/localisation', [SettingsController::class, 'updateLocalisation'])->middleware('permission:settings view');
     Route::post('/settings/signature-pad', [SettingsController::class, 'saveDigitalSignaturePad'])->middleware('permission:settings view');
     Route::post('/settings/update-phone', [SettingsController::class, 'updateProfilePhone'])->middleware('permission:settings view');
     Route::post('/settings/password-policies', [SettingsController::class, 'passwordPolicies'])->middleware('permission:settings security');
     Route::get('/settings/get-password-policies', [SettingsController::class, 'getPasswordPolicies'])->middleware('permission:settings security');
     Route::get('/settings/get-system-configurations', [SettingsController::class, 'getSystemConfigurations'])->middleware('permission:settings system');
     Route::post('/settings/system-configurations', [SettingsController::class, 'saveSystemConfigurations'])->middleware('permission:settings system');

     //integrations
     Route::get('/api/integrations/active', [IntegrationController::class, 'getActiveIntegrations']);
     Route::get('/api/integrations/custom', [IntegrationController::class, 'getCustomIntegrations']);
     Route::post('/api/integrations/custom', [IntegrationController::class, 'addCustomIntegration']);
     Route::get('/integrations/{provider}/redirect', [IntegrationController::class, 'redirect']);
     Route::get('/integrations/{provider}/callback', [IntegrationController::class, 'callback']);

     Route::get('/user-sessions', [SettingsController::class, 'getUserSession']);
     Route::get('/delete-user-sessions/{id}', [SettingsController::class, 'deleteUserSession']);
     Route::get('/logout-all-other-devices', [SettingsController::class, 'logOutAllOtherDevices']);
     Route::post('/enable-two-factor-authentication', [SettingsController::class, 'enableTwoFactorAuthentication']);
     Route::post('/disable-to-factor-authentication/{id}', [SettingsController::class, 'disableTwoFactorAuthentication']);

     //two factor authentication
     Route::get('/2fa', [SettingsController::class, 'twoFA']);
     Route::post('/update-user-2fa', [SettingsController::class, 'updateUser2FA']);

     Route::prefix('finance-and-accounts')->group(function(){
        Route::get('/', [FandAController::class, 'payments'])->name('f-and-a');
        Route::post('/set-registry-files', [RegistryController::class, 'setFandARegistryFiles']);
        Route::get('/get-registry-files', [FandAController::class, 'getRegistryFiles']);
        Route::post('/initiate-payment', [FandAController::class, 'initiatePayment']);
        Route::get('/get-division-heads', [FandAController::class, 'getDivisionHeads']);
        Route::get('/get-unit-heads', [FandAController::class, 'getUnitHeads']);
        Route::get('/get-active-payments', [FandAController::class, 'getActivePayments']);
        Route::get('/view-payment/{id}', [FandAController::class, 'viewPayment'])->name('view-payment');
        Route::post('/reject-payment', [FandAController::class, 'rejectPayment']);
        Route::post('/accept-division-payment', [FandAController::class, 'acceptDivisionPayment']);
        Route::get('/get-advanced-section-and-other-charges-users', [FandAController::class, 'getAdvancedSectionAndOtherChargesUsers']);
        Route::post('/assign-to-advance-other-section', [FandAController::class, 'assignToAdvanceOtherSection']);
        Route::post('/assign-to-advance-section', [FandAController::class, 'assignToAdvanceSection']);
        Route::get('/get-advance-section-users', [FandAController::class, 'getAdvanceSectionUsers']);
        Route::get('/get-checking-users', [FandAController::class, 'getCheckingUsers']);
        Route::get('/get-other-charges-users', [FandAController::class, 'getOtherChargesUsers']);
        Route::post('/assign-to-checking', [FandAController::class, 'assignToChecking']);
        Route::post('/advance-task-submit', [FandAController::class, 'advanceTaskSubmit']);
        Route::post('/other-charges-task-submit', [FandAController::class, 'otherChargesTaskSubmit']);
        Route::get('/get-other-charges-hos', [FandAController::class, 'getOtherChargesHOS']);
        Route::post('/assign-to-other-charges-by-hos', [FandAController::class, 'assignToOtherChargesByHOS']);
        Route::get('/get-payment-assignments', [FandAController::class, 'getPaymentAssignments']);
        Route::post('/payment-receipt-upload', [FandAController::class, 'paymentReceiptUpload']);
        Route::get('/remove-payment-receipt', [FandAController::class, 'removeUploadedReceipt']);
        Route::post('/finalise-payment', [FandAController::class, 'finalisePayment']);
        Route::get('/get-resolved-payments', [FandAController::class, 'getResolvedPayments']);
        Route::post('/re-submit-payment', [FandAController::class, 'reSubmitPayment']);

        //streamlined urls
        Route::post('/assign-to-section-users',[FandAController::class, 'assignToSectionUsers']);
        Route::post('/submitTask', [FandAController::class, 'submitTask']);
        Route::get('/get-section-users', [FandAController::class, 'getSectionUsers']);

        // Phase 2 Classification & Advances Lifecycle
        Route::post('/classify-payment', [FandAController::class, 'classifyPayment']);
        Route::get('/get-section-heads', [FandAController::class, 'getSectionHeads']);
        Route::get('/get-internal-audit-users', [FandAController::class, 'getInternalAuditUsers']);
        Route::get('/get-cash-office-users', [FandAController::class, 'getCashOfficeUsers']);
        Route::get('/get-final-accounts-users', [FandAController::class, 'getFinalAccountsUsers']);
        Route::get('/get-advances-ledger', [FandAController::class, 'getAdvancesLedger']);
        Route::post('/retire-advance', [FandAController::class, 'retireAdvance']);
        Route::get('/check-fr1420/{userId}', [FandAController::class, 'checkFR1420']);

        // Digital Stamps & Audit Queries
        Route::post('/affix-checking-stamp', [VoucherController::class, 'affixCheckingStamp']);
        Route::post('/affix-audit-stamp', [VoucherController::class, 'affixAuditStamp']);
        Route::post('/raise-audit-query', [VoucherController::class, 'raiseAuditQuery']);
        Route::post('/respond-audit-query', [VoucherController::class, 'respondAuditQuery']);
        Route::get('/get-payment-audit-queries/{paymentId}', [VoucherController::class, 'getAuditQueries']);


        //votebook
        Route::get('/vote-book', [VoteBookController::class, 'index'])->name('vote-book');
        Route::post('/create-vote-book', [VoteBookController::class, 'createVoteBook']);
        Route::post('/votebook-topup', [VoteBookController::class, 'voteBookTopUp']);
        Route::get('/get-votebooks', [VoteBookController::class, 'getVoteBooks']);
        Route::get('/get-votebook-ledger', [VoteBookController::class, 'getVoteBookLedger']);

        //ledger
        Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
        Route::post('/create-ledger-entry', [LedgerController::class, 'createLedgerEntry'])->name('create-ledger-entry');
        Route::get('/get-workflow', [LedgerController::class, 'getWorkflow'])->name('get-workflow');
        Route::get('/get-ledger/{id}', [LedgerController::class, 'getLedger'])->name('get-ledger');
        Route::get('/get-ledgers', [LedgerController::class, 'getLedgers']);
        Route::get('/download-report', [LedgerController::class, 'downloadReport']);

        //voucher
        Route::post('/create-voucher', [VoucherController::class, 'createVoucher']);
        Route::post('/generate-vat-voucher', [VoucherController::class, 'generateVATVoucher']);
        Route::post('/payment-save-modified-pdf', [VoucherController::class, 'saveModifiedPdf']);

        //comment
        Route::post('/make-comment', [FandAController::class, 'makeComment']);

        //Cashbook
        Route::get('/cashbook', [CashBookController::class, 'index']);
        Route::post('/create-cashbook', [CashBookController::class, 'createCashBook']);
        Route::post('/disburse-cash', [CashBookController::class, 'disburseCash']);
        Route::get('/get-cashbooks', [CashBookController::class, 'getCashbooks']);
        route::get('/download-cashbook-report', [CashBookController::class, 'downloadReport']);

        // General Ledger Auto-Posting (Final Accounts Unit)
        Route::post('/post-to-gl', [LedgerController::class, 'postToGeneralLedger']);
        Route::post('/post-retirement-to-gl', [LedgerController::class, 'postRetirementToGeneralLedger']);

        Route::get('/get-all-mails', function($query){
            
        });
     });

     // Finance & Accounts Root Route Aliases (Ensures backward compatibility and prevents 404 toasts)
     Route::get('/get-payment-audit-queries/{paymentId}', [VoucherController::class, 'getAuditQueries']);
     Route::post('/payment-save-modified-pdf', [VoucherController::class, 'saveModifiedPdf']);
     Route::post('/affix-checking-stamp', [VoucherController::class, 'affixCheckingStamp']);
     Route::post('/affix-audit-stamp', [VoucherController::class, 'affixAuditStamp']);
     Route::post('/raise-audit-query', [VoucherController::class, 'raiseAuditQuery']);
     Route::post('/respond-audit-query', [VoucherController::class, 'respondAuditQuery']);
     Route::post('/classify-payment', [FandAController::class, 'classifyPayment']);
     Route::post('/retire-advance', [FandAController::class, 'retireAdvance']);
     Route::get('/check-fr1420/{userId}', [FandAController::class, 'checkFR1420']);
     Route::post('/disburse-cash', [CashBookController::class, 'disburseCash']);
     Route::post('/post-to-gl', [LedgerController::class, 'postToGeneralLedger']);
     Route::get('/vote-book', [VoteBookController::class, 'index']);
     Route::get('/ledger', [LedgerController::class, 'index']);
     Route::get('/cashbook', [CashBookController::class, 'index']);
     Route::get('/available-designations', [DesignationController::class, 'getAvailableDesignations']);
     Route::post('/verify-signature-pin', [UserController::class, 'verifySignaturePin']);
     Route::get('/governance-hub', [GovernanceHubController::class, 'index']);

     // API Generic Workflow session fallback
     Route::prefix('api/generic-workflow')->group(function () {
         Route::get('/tasks/{taskId}/state', [GenericWorkflowInstanceController::class, 'getState']);
         Route::get('/tasks/{taskId}/actions', [GenericWorkflowInstanceController::class, 'getAvailableActions']);
         Route::post('/tasks/{taskId}/execute', [GenericWorkflowInstanceController::class, 'executeAction']);
         Route::post('/tasks/{taskId}/pause', [GenericWorkflowInstanceController::class, 'pauseTask']);
         Route::post('/tasks/{taskId}/resume', [GenericWorkflowInstanceController::class, 'resumeTask']);
         Route::post('/tasks/{taskId}/start-sub-workflow', [GenericWorkflowInstanceController::class, 'startSubWorkflow']);
     });
});
//Route::get('2fa-challenge', [TwoFactorChallengeController::class, 'show'])->name('2fa.challenge');

