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

});


Route::middleware('auth')->group(function () {
    Route::prefix('admin')->middleware('role:super admin| role:admin')->group(function(){
        Route::get('/',[AdminController::class, 'index']);
        Route::get('/branch-setup',[AdminController::class, 'BranchSetup']);
        Route::get('/meta-data-setup', [AdminController::class, 'MetaDataSetup']);
        Route::get('/setup-document-type', [AdminController::class, 'SetupDocumentType']);
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
        Route::get('/metadata-part', [MetadataController::class, 'getMetadataPart']);
    
        // document type
        Route::post('/create-document-type', [DocumentTypeController::class, 'createDocumentType']);
        Route::post('/configure-meta-data', [DocumentTypeController::class, 'configureMetaData']);

        //manage users
        Route::get('/manage-users', [AdminController::class, 'manageUsers']);
        Route::get('/add-user', [AdminController::class, 'addUser']);

        //user registration
        Route::post('/user-registration', [RegistrationController::class, 'UsersRegistration']);

         //roles
         Route::get('/assign-role-to-super-admin', [AdminController::class, 'assignRoleToSuperAdmin']);
        
    });

    //dashboard
    Route::prefix('dashboard')->middleware('role:data entry|super admin')->group(function(){
        route::get('/', [DashboardController::class, 'index']);
        route::get('/branch', [DashboardController::class, 'Branch']);
    });

    //archiving
    Route::get('/archiving', [ArchiveController::class, 'Archiving']);
    Route::get('/create-folder',[ArchiveController::class, 'createFolder']);
    Route::post('/upload-files', [ArchiveController::class, 'uploadFiles']);
    Route::post('/archive-files', [ArchiveController::class, 'ArchiveFiles']);
    Route::get('/delete-archive', [ArchiveController::class, 'deleteArchives']);
    Route::get('/download-file', [ArchiveController::class, 'downloadFile']);
    Route::post('/duplicate-file', [ArchiveController::class, 'DuplicateFile']);
    Route::post('/change-file-format', [ArchiveController::class, 'changeFileFormat']);

    //Branches
    Route::get('/branches/{id}', [ArchiveController::class, 'Branches']);

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
});
