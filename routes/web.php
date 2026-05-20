<?php

use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\Admin\KpiIndicatorController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeFormController;
use App\Http\Controllers\EmployeeSubmissionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/forms', [EmployeeFormController::class, 'index'])->name('forms.index');
    Route::get('/forms/{form}', [EmployeeFormController::class, 'show'])->name('forms.show');
    Route::post('/forms/{form}/submissions', [EmployeeFormController::class, 'store'])->name('forms.submissions.store');
    Route::get('/submissions', [EmployeeSubmissionController::class, 'index'])->name('submissions.index');

    Route::middleware('role:admin,owner')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/forms', [FormTemplateController::class, 'index'])->name('forms.index');
        Route::post('/forms', [FormTemplateController::class, 'store'])->name('forms.store');
        Route::post('/forms/{form}/versions', [FormTemplateController::class, 'newVersion'])->name('forms.versions.store');
        Route::patch('/forms/{form}/toggle', [FormTemplateController::class, 'toggle'])->name('forms.toggle');

        Route::get('/kpi', [KpiIndicatorController::class, 'index'])->name('kpi.index');
        Route::post('/kpi', [KpiIndicatorController::class, 'store'])->name('kpi.store');
        Route::patch('/kpi/{indicator}/toggle', [KpiIndicatorController::class, 'toggle'])->name('kpi.toggle');

        Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('/master-data/outlets', [MasterDataController::class, 'storeOutlet'])->name('master-data.outlets.store');
        Route::patch('/master-data/outlets/{outlet}/toggle', [MasterDataController::class, 'toggleOutlet'])->name('master-data.outlets.toggle');
        Route::post('/master-data/job-roles', [MasterDataController::class, 'storeJobRole'])->name('master-data.job-roles.store');
        Route::patch('/master-data/job-roles/{jobRole}/toggle', [MasterDataController::class, 'toggleJobRole'])->name('master-data.job-roles.toggle');
        Route::post('/master-data/employees', [MasterDataController::class, 'storeEmployee'])->name('master-data.employees.store');
        Route::patch('/master-data/employees/{employee}/toggle', [MasterDataController::class, 'toggleEmployee'])->name('master-data.employees.toggle');

        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{submission}', [ReviewController::class, 'update'])->name('reviews.update');
    });

    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});
