<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PractitionerController;
use App\Http\Controllers\OutMigrationController;
use App\Http\Controllers\Practitioner\CPDController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/portal', [PortalController::class, 'dashboard'])->name('portal.dashboard');
Route::get('/portal/profile', [PortalController::class, 'profile'])->name('portal.profile');
Route::post('/portal/proxy', [PortalController::class, 'proxy'])->name('portal.proxy');
Route::post('/portal/profile/update', [PortalController::class, 'updateProfile'])->name('portal.profile.update');

// Student Routes
Route::prefix('student')->group(function () {
    Route::get('/registration', [StudentController::class, 'registration'])->name('student.registration');
    Route::get('/examination', [StudentController::class, 'examination'])->name('student.examination');
    Route::get('/internship', [StudentController::class, 'internship'])->name('student.internship');
    Route::get('/indexing', [StudentController::class, 'indexing'])->name('student.indexing');
});

// Practitioner Routes
Route::prefix('practitioner')->group(function () {
    Route::get('/renewals', [PractitionerController::class, 'renewals'])->name('practitioner.renewals');
    Route::post('/renewals/process', [PractitionerController::class, 'processRenewal'])->name('practitioner.renewals.process');
    Route::get('/renewals/workstations', [PractitionerController::class, 'getWorkstations'])->name('practitioner.renewals.workstations');
    Route::get('/applications', [PractitionerController::class, 'getApplications'])->name('practitioner.applications');
    Route::get('/invoices', [PractitionerController::class, 'invoices'])->name('practitioner.invoices');
    Route::get('/invoices/{id}', [PractitionerController::class, 'invoiceDetails'])->name('practitioner.invoices.show');
    Route::match(['get','post'], '/pesaflow/callback', [PractitionerController::class, 'pesaflowCallback'])->name('practitioner.pesaflow.callback');
    Route::get('/outmigration', [PractitionerController::class, 'outmigration'])->name('practitioner.outmigration');
    Route::post('/outmigration/apply', [OutMigrationController::class, 'apply'])->name('practitioner.outmigration.apply');
    Route::get('/outmigration/invoices', [OutMigrationController::class, 'invoices'])->name('practitioner.outmigration.invoices');
    Route::get('/outmigration/invoices/{id}', [OutMigrationController::class, 'invoiceDetails'])->name('practitioner.outmigration.invoices.show');
    Route::get('/private-practice', [PractitionerController::class, 'privatePractice'])->name('practitioner.private-practice');
    // Private Practice module
    Route::post('/private-practice/apply', [\App\Http\Controllers\PrivatePracticeController::class, 'apply'])
        ->name('practitioner.private-practice.apply');
    Route::get('/private-practice/invoices', [\App\Http\Controllers\PrivatePracticeController::class, 'invoices'])
        ->name('practitioner.private-practice.invoices');
    Route::get('/private-practice/invoices/{id}', [\App\Http\Controllers\PrivatePracticeController::class, 'invoiceDetails'])
        ->name('practitioner.private-practice.invoices.show');
    Route::get('/cpd', [PractitionerController::class, 'cpd'])->name('practitioner.cpd');
});

#Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');
Route::get('/announcements', 'App\Http\Controllers\AnnouncementController@index')->name('announcements');
// Practitioner CPD Routes
Route::prefix('practitioner')->group(function () {
    Route::get('/cpd', [CPDController::class, 'index'])->name('practitioner.cpd');
    Route::get('/cpd/create', [CPDController::class, 'create'])->name('practitioner.cpd.create');
    Route::post('/cpd', [CPDController::class, 'store'])->name('practitioner.cpd.store');
    Route::post('/cpd/claim-token', [CPDController::class, 'claimToken'])->name('practitioner.cpd.claim-token');
});

// routes/web.php
Route::get('/practitioner/workstations', [PractitionerController::class, 'getWorkstations'])
    ->name('practitioner.workstations');
