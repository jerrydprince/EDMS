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
    Route::prefix('admin')->middleware('role:super admin| role:admin')->group(function(){
        
        
    });

        //archiving
       // Route::get('/archiving/{id}', [ArchiveController::class, 'Archiving']);
        Route::post('/upload-files', [ArchiveController::class, 'uploadFiles']);
        Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles']);
        Route::get('/delete-archive', [ArchiveController::class, 'deleteArchives']);
        Route::get('/download-file', [ArchiveController::class, 'downloadFile']);
        Route::post('/duplicate-file', [ArchiveController::class, 'DuplicateFile']);
        Route::post('/change-file-format', [ArchiveController::class, 'changeFileFormat']);
        Route::post('/upload-new-document', [ArchiveController::class, 'uploadNewDocument']);
        Route::post('/restore-file-version', [ArchiveController::class, 'restoreFileVersion']);
        Route::post('/lock-document', [ArchiveController::class, 'lockDocument']);
        Route::get('/delete-file-version', [ArchiveController::class, 'deleteFileVersion']);
        Route::get('/share-file-search', [ArchiveController::class, 'shareFileSearch']);
        Route::post('/share-file', [ArchiveController::class, 'shareFile']);

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
            Route::middleware('role:super admin')->group(function () {
                Route::get('/',[AdminController::class, 'index']);
                Route::get('/branch-setup',[AdminController::class, 'BranchSetup']);
                Route::get('/metadata-setup', [AdminController::class, 'MetaDataSetup']);
            
                Route::post('/create-department-division-unit', [AdminController::class, 'CreateDepartmentDivisionUnit']);
                Route::post('/delete-department', [AdminController::class, 'deleteDepartment']);
                //Route::post('/duplicate-department', [AdminController::class, 'duplicateDepartment']);
                Route::get('/get-divisions', [AdminController::class, 'getDivision']);
                Route::post('/delete-division', [AdminController::class, 'deleteDivision']);
            // Route::post('/duplicate-division', [AdminController::class, 'duplicateDivision']);
                Route::post('/add-document-type-to-department', [AdminController::class, 'addDocumentTypeToDepartment']);
                Route::post('/add-document-type-to-division', [AdminController::class, 'addDocumentTypeToDivision']);
                Route::post('/add-document-type-to-unit', [AdminController::class, 'addDocumentTypeToUnit']);
                Route::post('/add-division-to-department', [AdminController::class, 'AddDivisionToDepartment']);
                Route::post('/edit-department', [AdminController::class, 'editDepartment']);
                Route::get('/get-units', [AdminController::class, 'getUnits']);
                Route::post('/delete-unit', [AdminController::class, 'deleteUnit']);
                //Route::post('/duplicate-unit', [AdminController::class, 'duplicateUnit']);
                Route::post('/add-unit-to-division', [AdminController::class, 'addUnitToDivision']);
                Route::post('/edit-division', [AdminController::class, 'editDivision']);
                Route::post('/edit-unit', [AdminController::class,'editUnit']);
                Route::get('/search-department-head', [AdminController::class, 'searchDepartmentHead']);
                Route::get('/search-division-head', [AdminController::class, 'searchDivisionHead']);
                Route::get('/search-unit-head', [AdminController::class, 'searchUnitHead']);
            
                // metadata
                Route::post('/create-metadata',[MetadataController::class, 'createMetaData']);
                Route::post('/delete-metadata', [MetadataController::class, 'deleteMetaData']);
                Route::get('/metadatas', [MetadataController::class, 'getMetadata']);
                Route::get('/metadata-part', [MetadataController::class, 'getMetadata']);
            
                // document type
                Route::post('/create-document-type', [DocumentTypeController::class, 'createDocumentType']);
                Route::get('/setup-document-type', [DocumentTypeController::class, 'SetupDocumentType']);
                Route::post('/configure-meta-data', [DocumentTypeController::class, 'configureMetaData']);
                Route::post('/duplicate-document-type', [DocumentTypeController::class, 'duplicateDocumentType']);
                Route::get('/delete-document-type', [DocumentTypeController::class, 'deleteDocumentType']);

                //file type
                Route::get('/file-type', [AdminController::class, 'fileType']);
                Route::post('/file-type', [AdminController::class, 'createFileType']);
                Route::get('/get-file-type', [AdminController::class, 'getFileType']);
                Route::get('/delete-file-type', [AdminController::class, 'deleteFileType']);
                Route::post('/assign-file-type-to-department', [AdminController::class, 'assignFileTypesToBranch']);

                //manage users
                Route::get('/manage-users', [UserController::class, 'manageUsers']);
                Route::get('/add-user', [UserController::class, 'addUser']);
                Route::get('/edit-user', [UserController::class, 'editUser']);
                Route::post('/edit-user', [UserController::class, 'postEditUser']);
                Route::get('/role-users', [UserController::class, 'RoleUsers']);
                Route::get('/delete-user/{id}', [UserController::class, 'deleteUser']);

                //user registration
                Route::post('/user-registration', [RegistrationController::class, 'UsersRegistration']);

                //roles
                Route::get('/assign-role-to-super-admin', [AdminController::class, 'assignRoleToSuperAdmin']);

                //Branches
                //Route::get('/branches', [ArchiveController::class, 'Branches']);
                Route::get('/branches/{branch}/{id}', [ArchiveController::class, 'userBranches']);

                //archiving
                Route::post('/upload-files', [ArchiveController::class, 'uploadFiles']);
                Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles']);
                Route::get('/archiving/{id}/{folder_id}', [ArchiveController::class, 'Archiving']);
                Route::get('/create-folder',[ArchiveController::class, 'createFolder']);
                Route::post('/close-folder{id}', [ArchiveController::class, 'closeFolder']);
                Route::get('/get-division-with-unit', [ArchiveController::class, 'getDivisionWithUnit']);
                Route::get('/folder-view/{branch}/{id}', [ArchiveController::class, 'folderView']);

                //Mails
                Route::get('/mails', [MailController::class, 'mail']);
                Route::get('/mails/ReadMail/{emailId}', [MailController::class, 'readMail']);
                Route::get('/mails/DraftMail/{emailId}', [MailController::class, 'draftMail']);
                Route::post('/mails', [MailController::class, 'createMail']);
                Route::get('/mails/archiveMails', [MailController::class, 'getArchiveMails']);
                Route::get('/mails/SingleMail/{emailId}', [MailController::class, 'getSingleMail']);
                Route::get('/mails/SingleDraftMail/{emailId}', [MailController::class, 'getSingleDraftMail']);
                Route::get('/mails/starredMails', [MailController::class, 'getStarredMails']);
                Route::post('/mails/archive/{emailId}', [MailController::class, 'archiveMail']);
                Route::post('/mails/star/{emailId}', [MailController::class, 'starMail']);
                Route::post('/mails/drafts', [MailController::class, 'isDraft']);
                Route::get('/mails/drafts', [MailController::class, 'getDrafts']);

                //workflow
                Route::get('/workflow', [WorkFlowController::class, 'index']);
                Route::post('/workflow', [WorkFlowController::class, 'createWorkFlow']);

                //task
                Route::post('/create-task', [TaskController::class, 'createTask']);
                Route::get('/task-by-status', [TaskController::class, 'getTaskByStatus']);

                //users
                Route::get('/search-users', [UserController::class, 'searchUsers']);
            });
        
           
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
