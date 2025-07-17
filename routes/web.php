<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AlbumPhotoController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth::routes();
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('members.index') // if logged in
        : redirect()->route('login');        // if not logged in
});

Route::middleware(['auth'])->group(function () {
    Route::resource('/members', MemberController::class)->except('create', 'show');
    Route::resource('/standing-committee', CommitteeController::class)->except('create', 'show');
    Route::resource('/district-assignments', AssignmentController::class)->except('create', 'show');
    Route::resource('/photo-journals', AlbumController::class);
    Route::get('/photo-journals/photos/{id}', [AlbumPhotoController::class, 'index'])->name('photo.index');
    Route::put('/photo-journals/photos/{id}', [AlbumPhotoController::class, 'update'])->name('photo.update');
    Route::post('/photo-journals/photos/store', [AlbumPhotoController::class, 'store'])->name('photo.store');
    Route::delete('/photo-journals/photos/{id}', [AlbumPhotoController::class, 'destroy'])
        ->name('photo.destroy');
    Route::resource('/organization', OrganizationController::class)->except('create', 'show');
    Route::get('api/photo-journals/photos/load/{id}', [AlbumPhotoController::class, 'loadImages'])
        ->name('photo.load');
    Route::resource('/calendar-event', CalendarEventController::class)->except('create', 'show');
    Route::resource('/barangay-officials', BarangayController::class)->except('create', 'show');
    

    Route::get('api/org-chart/members', [OrganizationController::class, 'loadNodes']);
});

Route::middleware('api')->prefix('api')->group(function () {
    Route::get('/members', [MemberController::class, 'loadData']);
    Route::get('/standing-committee', [CommitteeController::class, 'loadData']);
    Route::get('/district-assignments', [AssignmentController::class, 'loadData']);
    Route::get('/organization', [OrganizationController::class, 'loadData']);
    Route::get('/photo-journals', [AlbumController::class, 'loadData']);
    Route::get('/photo-journals/photos/{id}', [AlbumPhotoController::class, 'loadImages']);
    Route::get('/calendar-event', [CalendarEventController::class, 'loadData']);
    Route::get('/barangay-officials', [BarangayController::class, 'loadData']);

});