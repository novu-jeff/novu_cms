<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AlbumPhotoController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\BarangayOfficialController;
use App\Http\Controllers\GalleriesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecretaryDocumentController;
use App\Http\Controllers\SecretaryAgendaController;
use App\Http\Controllers\SessionMeetingController;
use App\Http\Controllers\LiveSessionController;
use App\Http\Controllers\SecretaryMinutesController;
use App\Http\Controllers\SecretaryRemarkController;
use App\Http\Controllers\WhitepaperController;


Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('/whitepaper', [WhitepaperController::class, 'index'])->name('whitepaper');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('members.index') // if logged in
        : redirect()->route('login');        // if not logged in
});

Route::middleware(['auth'])->group(function () {
    Route::resource('/members', MemberController::class)->except('create', 'show');
     Route::get('/members/{member}/account', [MemberController::class, 'account'])->name('members.account.show');
    Route::post('/members/{member}/account', [MemberController::class, 'saveAccount'])->name('members.account.save');
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
   // Route::resource('/barangay-officials', BarangayController::class)->except('create', 'show');

    

    Route::get('/barangay-officials', [BarangayOfficialController::class, 'index'])->name('barangay-officials.index');
    Route::post('/barangay-officials', [BarangayOfficialController::class, 'store'])->name('barangay-officials.store');

    Route::resource('barangay-officials', BarangayOfficialController::class);

    Route::post('/barangay-officials/reorder', [BarangayOfficialController::class, 'updateOrder'])->name('barangay-officials.reorder');


    // API route for searching officials by barangay
    Route::get('/api/barangays/{id}/officials', [BarangayOfficialController::class, 'getByBarangay']);

    

    Route::get('api/org-chart/members', [OrganizationController::class, 'loadNodes']);

    Route::get('/galleries', [GalleriesController::class, 'index'])->name('gallery.index');
    Route::post('/upload', [GalleriesController::class, 'store'])->name('gallery.store');
    
    Route::post('/members/reorder', [MemberController::class, 'updateOrder'])->name('members.reorder');

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

    Route::prefix('secretary')->group(function () {

        Route::get('/documents', 
            [SecretaryDocumentController::class,'index']
        )->name('secretary.documents');
    
        Route::post('/documents/{id}/approve',
            [SecretaryDocumentController::class,'approve']
        )->name('secretary.documents.approve');
    
        Route::post('/documents/{id}/reject',
            [SecretaryDocumentController::class,'reject']
        )->name('secretary.documents.reject');

        Route::post('/documents/{id}/update-session',
            [SecretaryDocumentController::class, 'updateSession']
        )->name('secretary.documents.updateSession');

        Route::resource('session-meetings', SessionMeetingController::class);

        Route::get('/session/{id}/agenda',
        [SecretaryAgendaController::class,'index']
        )->name('secretary.session.agenda');

        Route::post('/session/agenda/reorder',
            [SecretaryAgendaController::class,'reorder']
        )->name('secretary.session.agenda.reorder');

        Route::get('/session/{id}/packet',
            [SecretaryAgendaController::class,'packet']
        )->name('secretary.session.packet');

        Route::get('/session/{id}/minutes-template',
            [SecretaryAgendaController::class,'minutesTemplate']
            )->name('secretary.session.minutes');

        Route::get(
            '/session/{id}/minutes',
            [SecretaryMinutesController::class,'index']
            )->name('secretary.minutes.index');
            
        Route::post(
            '/session/{id}/minutes',
            [SecretaryMinutesController::class,'store']
            )->name('secretary.minutes.store'); 
            
        Route::get(
            '/session/{id}/minutes-generate',
            [SecretaryMinutesController::class,'generate']
            )->name('secretary.minutes.generate');   
            
        Route::get(
            '/secretary/session/{id}/minutes-review',
            [SecretaryMinutesController::class,'review']
            )->name('secretary.minutes.review');  
            
        Route::get(
                '/session/{id}/remarks',
                [SecretaryRemarkController::class,'index']
                )->name('secretary.remarks.index');    

        // routes/web.php

    Route::get('/settings/live-session', [\App\Http\Controllers\LiveSessionController::class, 'editLive'])
        ->name('settings.live.edit');
    Route::post('/settings/live-session', [\App\Http\Controllers\LiveSessionController::class, 'updateLive'])
        ->name('settings.live.update');

    
    });

});

Route::middleware('api')->prefix('api')->group(function () {
    Route::get('/members', [MemberController::class, 'loadData']);
    Route::get('/standing-committee', [CommitteeController::class, 'loadData']);
    Route::get('/district-assignments', [AssignmentController::class, 'loadData']);
    Route::get('/organization', [OrganizationController::class, 'loadData']);
    Route::get('/photo-journals', [AlbumController::class, 'loadData']);
    Route::get('/photo-journals/photos/{id}', [AlbumPhotoController::class, 'loadImages']);
    Route::get('/calendar-event', [CalendarEventController::class, 'loadData']);
   // Route::get('/barangay-officials', [BarangayController::class, 'loadData']);
    

    Route::get('/barangay-officials', [BarangayOfficialController::class, 'loadData']);
    Route::get('/photos', [GalleriesController::class, 'apiIndex']);
    Route::get('/gal-photos', [GalleriesController::class, 'apiFrontGal']);
    Route::delete('/photo/{id}', [GalleriesController::class, 'destroy']);
    Route::patch('/photo/{id}/toggle', [GalleriesController::class, 'toggleActive']);
    Route::post('/photo/toggle/{id}', [GalleriesController::class, 'togglePhotoActive']);



});