<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    });

    Route::middleware('role:superadmin')->group(function () {
        Route::get('/superadmin/dashboard', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');
        Route::get('/superadmin/tenants', [SuperAdminController::class, 'tenants'])->name('superadmin.tenants');
        Route::get('/superadmin/tenants/create', [SuperAdminController::class, 'tenantsCreate'])->name('superadmin.tenants.create');
        Route::post('/superadmin/tenants', [SuperAdminController::class, 'tenantsStore'])->name('superadmin.tenants.store');
        Route::get('/superadmin/tenants/{tenant}/edit', [SuperAdminController::class, 'tenantsEdit'])->name('superadmin.tenants.edit');
        Route::put('/superadmin/tenants/{tenant}', [SuperAdminController::class, 'tenantsUpdate'])->name('superadmin.tenants.update');
        Route::patch('/superadmin/tenants/{tenant}/toggle', [SuperAdminController::class, 'tenantsToggleStatus'])->name('superadmin.tenants.toggle');
        Route::get('/superadmin/security', [SuperAdminController::class, 'security'])->name('superadmin.security');
        Route::put('/superadmin/security/password', [SuperAdminController::class, 'updatePassword'])->name('superadmin.security.password');
        Route::get('/superadmin/seduc/create', [SuperAdminController::class, 'seducCreate'])->name('superadmin.seduc.create');
        Route::post('/superadmin/seduc', [SuperAdminController::class, 'seducStore'])->name('superadmin.seduc.store');
        Route::get('/superadmin/seducs', [SuperAdminController::class, 'seducs'])->name('superadmin.seducs');
        Route::get('/superadmin/seducs/{user}/edit', [SuperAdminController::class, 'seducsEdit'])->name('superadmin.seducs.edit');
        Route::put('/superadmin/seducs/{user}', [SuperAdminController::class, 'seducsUpdate'])->name('superadmin.seducs.update');
        Route::post('/superadmin/seducs/{user}/reset-password', [SuperAdminController::class, 'seducsResetPassword'])->name('superadmin.seducs.reset-password');
    });
    
    Route::middleware('role:semed')->group(function () {
        Route::get('/semed/dashboard', [DashboardController::class, 'semed'])->name('semed.dashboard');
        Route::get('/semed/security', [DashboardController::class, 'semedSecurity'])->name('semed.security');
        Route::put('/semed/security/password', [DashboardController::class, 'semedUpdatePassword'])->name('semed.security.password');
    });
    
    Route::middleware('role:director,coordinator')->group(function () {
        Route::get('/school/dashboard', [DashboardController::class, 'school'])->name('school.dashboard');
        
        // Planning
        Route::get('/school/plannings', [SchoolController::class, 'plannings'])->name('school.plannings');
        Route::get('/school/planning/create', [SchoolController::class, 'createPlanning'])->name('school.planning.create');
        Route::post('/school/planning', [SchoolController::class, 'storePlanning'])->name('school.planning.store');
        Route::get('/school/planning/view', [SchoolController::class, 'viewPlanning'])->name('school.planning.view');
        Route::get('/school/planning/edit', [SchoolController::class, 'editPlanning'])->name('school.planning.edit');
        Route::put('/school/planning', [SchoolController::class, 'updatePlanning'])->name('school.planning.update');
        Route::delete('/school/planning', [SchoolController::class, 'deletePlanning'])->name('school.planning.delete');

        // Classes
        Route::get('/school/classes', [SchoolController::class, 'classes'])->name('school.classes');
        Route::post('/school/class', [SchoolController::class, 'storeClass'])->name('school.class.store');
        Route::get('/school/class/edit', [SchoolController::class, 'editClass'])->name('school.class.edit');
        Route::put('/school/class', [SchoolController::class, 'updateClass'])->name('school.class.update');
        Route::delete('/school/class', [SchoolController::class, 'deleteClass'])->name('school.class.delete');

        // Professors
        Route::get('/school/professors', [SchoolController::class, 'professors'])->name('school.professors');
        Route::post('/school/professor', [SchoolController::class, 'storeProfessor'])->name('school.professor.store');
        Route::get('/school/professor/edit', [SchoolController::class, 'editProfessor'])->name('school.professor.edit');
        Route::put('/school/professor', [SchoolController::class, 'updateProfessor'])->name('school.professor.update');
        Route::delete('/school/professor', [SchoolController::class, 'deleteProfessor'])->name('school.professor.delete');
        Route::post('/school/professor/reset-password', [SchoolController::class, 'resetProfessorPassword'])->name('school.professor.reset-password');

        // Document Review
        Route::post('/school/document/review', [SchoolController::class, 'reviewDocument'])->name('school.document.review');

        // Bimesters
        Route::post('/school/planning/bimester', [SchoolController::class, 'associateToBimester'])->name('school.planning.bimester');

        // Photo/Password/Notification settings
        Route::post('/school/password/change', [SchoolController::class, 'changePassword'])->name('school.password.change');
        Route::post('/school/photo/upload', [SchoolController::class, 'uploadPhoto'])->name('school.photo.upload');
        Route::post('/school/uploads/viewed', [SchoolController::class, 'markUploadsAsViewed'])->name('school.uploads.viewed');

        // Coordinator management (Director only)
        Route::middleware('role:director')->group(function () {
            Route::post('/school/coordinator', [SchoolController::class, 'storeCoordinator'])->name('school.coordinator.store');
            Route::get('/school/coordinator/edit', [SchoolController::class, 'editCoordinator'])->name('school.coordinator.edit');
            Route::put('/school/coordinator', [SchoolController::class, 'updateCoordinator'])->name('school.coordinator.update');
            Route::post('/school/coordinator/reset-password', [SchoolController::class, 'resetCoordinatorPassword'])->name('school.coordinator.reset-password');
            Route::delete('/school/coordinator', [SchoolController::class, 'deleteCoordinator'])->name('school.coordinator.delete');
        });

        // Reports
        Route::get('/school/reports', [SchoolController::class, 'reports'])->name('school.reports');
    });
    
    Route::middleware('role:professor')->group(function () {
        Route::get('/professor/dashboard', [DashboardController::class, 'professor'])->name('professor.dashboard');
        Route::post('/professor/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('professor.documents.store');
    });
    
    Route::middleware('role:supervisor_edfis')->group(function () {
        Route::get('/supervisor-edfis/dashboard', [DashboardController::class, 'supervisorEdfis'])->name('supervisor-edfis.dashboard');
    });
    
    Route::middleware('role:supervisor_monitor')->group(function () {
        Route::get('/supervisor-monitor/dashboard', [DashboardController::class, 'supervisorMonitor'])->name('supervisor-monitor.dashboard');
    });

    // RAG AI Endpoints
    Route::post('/api/rag.php', [\App\Http\Controllers\RAGController::class, 'query']);
    Route::get('/api/rag.php', [\App\Http\Controllers\RAGController::class, 'history']);
    Route::post('/api/rag', [\App\Http\Controllers\RAGController::class, 'query'])->name('api.rag.query');
    Route::get('/api/rag', [\App\Http\Controllers\RAGController::class, 'history'])->name('api.rag.history');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
