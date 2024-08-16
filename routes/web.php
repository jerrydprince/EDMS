<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\MetadataController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\WorkFlowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\RegistryController;
use App\Http\Controllers\ChatController;

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
    return view('welcome');
});

//login
Route::middleware('guest')->group(function(){
    Route::get('/login',[LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('post-login');
    Route::get('/register', [RegistrationController::class, 'index'])->name('registration');
    Route::post('/register', [RegistrationController::class, 'register']);
    Route::get('/resend-verification-code', [RegistrationController::class, 'resendVerificationCode']);
    Route::post('/email-verification', [RegistrationController::class, 'emailVerifiation']);
    Route::get('/email-exist', [RegistrationController::class, 'checkMailExit']);
    Route::get('/phone-exist', [RegistrationController::class, 'checkPhoneExist']);
    Route::get('/password/reset/{email}', [PasswordController::class, 'passwordReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'ResetUserPassword']);

});


Route::middleware('auth')->group(function () {
    // Route::prefix('admin')->middleware('role:super admin| role:admin')->group(function(){
        
        
    // });

        //archiving
       // Route::get('/archiving/{id}', [ArchiveController::class, 'Archiving']);
        Route::post('/upload-files', [ArchiveController::class, 'uploadFiles'])->middleware('permission:archiving create');
        Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles'])->middleware('permission:archiving create');
        Route::get('/delete-archive', [ArchiveController::class, 'deleteArchives'])->middleware('permission:archiving delete');
        Route::get('/download-file', [ArchiveController::class, 'downloadFile'])->middleware('permission:archiving view');
        Route::post('/duplicate-file', [ArchiveController::class, 'DuplicateFile'])->middleware('permission:archiving view');
        Route::post('/change-file-format', [ArchiveController::class, 'changeFileFormat'])->middleware('permission:archiving update');
        Route::post('/upload-new-document', [ArchiveController::class, 'uploadNewDocument'])->middleware('permission:archiving create');
        Route::post('/restore-file-version', [ArchiveController::class, 'restoreFileVersion'])->middleware('permission:archiving update');
        Route::post('/lock-document', [ArchiveController::class, 'lockDocument'])->middleware('permission:archiving update');
        Route::get('/delete-file-version', [ArchiveController::class, 'deleteFileVersion'])->middleware('permission:archiving delete');
        Route::get('/share-file-search', [ArchiveController::class, 'shareFileSearch'])->middleware('permission:archiving view');
        Route::post('/share-file', [ArchiveController::class, 'shareFile'])->middleware('permission:archiving view');

        Route::prefix('data-entry')->group(function(){
            Route::middleware('role:data entry|super admin')->group(function () {
                Route::get('/', [DashboardController::class, 'index']);
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
                Route::get('/support', [DashboardController::class, 'support']);
    
                //file type
                Route::get('/get-branch-file-type', [ArchiveController::class, 'getBranchFileType']);
    
                //settings
                Route::get('/settings', [DashboardController::class, 'settings']);
                Route::get('/profile', [DashboardController::class, 'settings']);
                Route::get('/preferences', [DashboardController::class, 'settings']);
                Route::get('/security', [DashboardController::class, 'settings']);
                Route::get('/localisation', [DashboardController::class, 'settings']);
    
                //metadata
                Route::get('/metadatas', [MetadataController::class, 'getMetadata']);
                Route::get('/branch-metdata', [MetadataController::class, 'getBranchMetadata']);
    
                //users
                Route::post('/update-profile-image', [UserController::class, 'updateProfileImage']);
    
            });
        });

        //dashboard
        Route::prefix('dashboard')->group(function(){
            
            /**
             * pasted old admin route here
             */
            //Route::middleware()->group(function () {
                Route::get('/',[AdminController::class, 'index']);
                Route::get('/branch-setup',[AdminController::class, 'BranchSetup'])->middleware('permission:branch setup create');
                Route::get('/metadata-setup', [AdminController::class, 'MetaDataSetup'])->middleware('permission:metadata setup create');
            
                // Route::post('/create-department-division-unit', [AdminController::class, 'CreateDepartmentDivisionUnit'])->middleware('permission:branch setup create');
                Route::post('/delete-department', [AdminController::class, 'deleteDepartment'])->middleware('permission:branch setup delete');
                //Route::post('/duplicate-department', [AdminController::class, 'duplicateDepartment']);
                Route::get('/get-divisions', [AdminController::class, 'getDivision'])->middleware('permission:branch setup create');
                Route::post('/delete-division', [AdminController::class, 'deleteDivision'])->middleware('permission:branch setup create');
            // Route::post('/duplicate-division', [AdminController::class, 'duplicateDivision']);
                Route::post('/add-document-type-to-department', [AdminController::class, 'addDocumentTypeToDepartment'])->middleware('permission:branch setup create');
                Route::post('/add-document-type-to-division', [AdminController::class, 'addDocumentTypeToDivision'])->middleware('permission:branch setup create');
                Route::post('/add-document-type-to-unit', [AdminController::class, 'addDocumentTypeToUnit'])->middleware('permission:branch setup create');
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
            
                // document type
                Route::post('/create-document-type', [DocumentTypeController::class, 'createDocumentType'])->middleware('permission:document type create');
                Route::get('/setup-document-type', [DocumentTypeController::class, 'SetupDocumentType'])->middleware('permission:document type create');
                Route::post('/configure-meta-data', [DocumentTypeController::class, 'configureMetaData'])->middleware('permission:metadata update');
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
                Route::get('/add-user', [UserController::class, 'addUser'])->middleware('permission:manage users create');
                Route::get('/edit-user', [UserController::class, 'editUser'])->middleware('permission:manage users update');
                Route::post('/edit-user', [UserController::class, 'postEditUser'])->middleware('permission:manage users update');
                Route::get('/role-users', [UserController::class, 'RoleUsers']);
                Route::get('/delete-user/{id}', [UserController::class, 'deleteUser'])->middleware('permission:manage users delete');

                //user registration
                Route::post('/user-registration', [RegistrationController::class, 'UsersRegistration']);

                //roles
                Route::get('/assign-role-to-super-admin', [AdminController::class, 'assignRoleToSuperAdmin']);
                Route::post('/add-permissions-to-role', [RoleController::class, 'addPermissionToRole']);

                //Branches
                //Route::get('/branches', [ArchiveController::class, 'Branches']);
                Route::get('/branches/{branch}/{id}', [ArchiveController::class, 'userBranches']);

                //archiving
                Route::post('/upload-files', [ArchiveController::class, 'uploadFiles'])->middleware('permission:archiving create');
                Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles'])->middleware('permission:archiving create');
                Route::get('/archiving/{id}/{folder_id}', [ArchiveController::class, 'Archiving'])->middleware('permission:archiving create');
                Route::get('/create-folder',[ArchiveController::class, 'createFolder'])->middleware('permission:archiving create');
                Route::post('/close-folder{id}', [ArchiveController::class, 'closeFolder'])->middleware('permission:archiving create');
                Route::get('/get-division-with-unit', [ArchiveController::class, 'getDivisionWithUnit'])->middleware('permission:archiving create');
                Route::get('/folder-view/{branch}/{id}/{branch_id?}', [ArchiveController::class, 'folderView'])->middleware('permission:archiving create');

                //Mails
                Route::get('/mails', [MailController::class, 'mail'])->middleware('permission:mails view');
                Route::get('/mails/ReadMail/{emailId}', [MailController::class, 'readMail'])->middleware('permission:mails view');
                Route::get('/mails/DraftMail/{emailId}', [MailController::class, 'draftMail'])->middleware('permission:mails view');
                Route::post('/mails', [MailController::class, 'createMail'])->middleware('permission:mails create');
                Route::get('/mails/archiveMails', [MailController::class, 'getArchiveMails'])->middleware('permission:mails create');
                Route::get('/mails/SingleMail/{emailId}', [MailController::class, 'getSingleMail'])->middleware('permission:mails view');
                Route::get('/mails/SingleDraftMail/{emailId}', [MailController::class, 'getSingleDraftMail'])->middleware('permission:mails view');
                Route::get('/mails/starredMails', [MailController::class, 'getStarredMails'])->middleware('permission:mails view');
                Route::post('/mails/archive/{emailId}', [MailController::class, 'archiveMail'])->middleware('permission:mails update');
                Route::post('/mails/star/{emailId}', [MailController::class, 'starMail'])->middleware('permission:mails update');
                Route::post('/mails/drafts', [MailController::class, 'isDraft'])->middleware('permission:mails create');
                Route::get('/mails/drafts', [MailController::class, 'getDrafts'])->middleware('permission:mails create');
                Route::get('/mails/delete/{id}', [MailController::class, 'deleteMail'])->middleware('permission:mails delete');

                //workflow
                Route::get('/workflow', [WorkFlowController::class, 'index'])->middleware('permission:workflow view');
                Route::post('/workflow', [WorkFlowController::class, 'createWorkFlow'])->middleware('permission:workflow create');

                //task
                Route::post('/create-task', [TaskController::class, 'createTask'])->middleware('permission:task create');
                Route::get('/task-by-status', [TaskController::class, 'getTaskByStatus'])->middleware('permission:task view');
                Route::get('/view-task/{id}', [TaskController::class, 'viewTask'])->middleware('permission:task view');
                Route::get('/download-task-file/{file_name}', [TaskController::class, 'downloadFile'])->middleware('permission:task view');
                Route::post('/task-comment', [TaskController::class, 'taskComment'])->middleware('permission:task view');

                //users
                Route::get('/search-users', [UserController::class, 'searchUsers']);

                //chat
                Route::get('/chat', [ChatController::class, 'index'])->middleware('permission:chat view');
                Route::post('/chat', [ChatController::class, 'chat'])->middleware('permission:chat create');

                //Access level or Roles
                Route::get('/access-level', [RoleController::class, 'getAccessLevel'])->middleware('permission:access config view');
                Route::post('/change-accessLevel-status', [RoleController::class, 'toggleStatus'])->middleware('permission:access config update');
                Route::post('/create-role', [RoleController::class, 'createRole'])->middleware('permission:access config update');
                Route::get('/delete-level', [RoleController::class, 'deleteAccessLevel'])->middleware('permission:access config update');
           // });
        

              //REGISTRY
                Route::get('/admin-registry', [RegistryController::class, 'adminRegistry'])->middleware('permission:registry view');
                // Route::get('/registry/entry/login', [RegistryController::class, 'login'])->middleware('permission:registry view');
                Route::get('/registry', [RegistryController::class, 'registry'])->middleware('permission:registry view');
                Route::get('/registry/dept', [RegistryController::class, 'getDepartment']);
                // Route::get('/registry/deptFiles/{deptId}', [RegistryController::class, 'getFilesByDept']);
                Route::get('/registry/branchFiles', [RegistryController::class, 'getBranchFiles'])->middleware('permission:registry view');
                Route::post('/registry/newfile', [RegistryController::class, 'createFile']);
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
});
