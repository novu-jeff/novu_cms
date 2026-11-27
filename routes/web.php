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
use App\Http\Controllers\SessionControlController;
use App\Http\Controllers\SessionVotingController;
use App\Http\Controllers\PresidingOfficerMinutesController;
use App\Http\Controllers\OrganizationSettingController;
use App\Http\Controllers\CmsDashboardController;
use App\Http\Controllers\Secretary\SessionRecordingController;
use App\Http\Controllers\CommitteeReferralController;
use App\Http\Controllers\CommitteePortalAuthController;
use App\Http\Controllers\CommitteePortalController;
use App\Http\Controllers\CommitteePortalDocumentController;
use App\Http\Controllers\CommitteeMeetingController;
use App\Http\Controllers\CommitteePortal\LiveCommitteeSessionController;
use App\Http\Controllers\DocumentSigningController;
use App\Http\Controllers\PresidingOfficer\LiveDeliberationController;
use App\Http\Controllers\SecretaryNotificationController;



Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('/whitepaper', [WhitepaperController::class, 'index'])->name('whitepaper');
Route::get('/whitepaper.docx', [WhitepaperController::class, 'download'])->name('whitepaper.docx');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('members.index') // if logged in
        : redirect()->route('login');        // if not logged in
});

// Committee Member Portal Authentication
Route::prefix('committee-portal')->name('committee-portal.')->group(function () {

    Route::get('/login', [
        CommitteePortalAuthController::class,
        'showLogin'
    ])->name('login');

    Route::post('/login', [
        CommitteePortalAuthController::class,
        'login'
    ])->middleware('throttle:5,1')->name('login.submit');

    Route::post('/logout', [
        CommitteePortalAuthController::class,
        'logout'
    ])->middleware('auth:member')->name('logout');

    Route::middleware('auth:member')->group(function () {

        Route::get('/dashboard', [
            CommitteePortalController::class,
            'dashboard'
        ])->name('dashboard');

        Route::get('/referrals/{referralId}/document', [
            CommitteePortalDocumentController::class,
            'show'
        ])->whereNumber('referralId')->name('referrals.document');

        Route::get('/referrals/{referralId}', [CommitteePortalController::class, 'show'])
        ->whereNumber('referralId')
        ->name('referrals.show');

        Route::post('/referrals/{referralId}/start-review', [
            CommitteePortalController::class,
            'startReview'
        ])
            ->whereNumber('referralId')
            ->name('referrals.start-review');

        Route::post('/referrals/{referralId}/submit-report', [
            CommitteePortalController::class,
            'submitReport'
        ])
            ->whereNumber('referralId')
            ->name('referrals.submit-report');  
            
        Route::get('/live-session', [
            LiveCommitteeSessionController::class,
            'index'
        ])->name('live-session.index');

        Route::get('/live-session/{meeting}', [
            LiveCommitteeSessionController::class,
            'show'
        ])->name('live-session.show');

        Route::get('/live-session/{meeting}/state', [
            LiveCommitteeSessionController::class,
            'state'
        ])->name('live-session.state');


        Route::get(
            '/referrals/{referralId}/meeting-reports/{reportId}/download',
            [CommitteePortalController::class, 'downloadMeetingReport']
        )->whereNumber('referralId')
        ->whereNumber('reportId')
->name('referrals.meeting-reports.download');  

        // Committee Portal Electronic Signing
    Route::prefix('electronic-signing')
        ->name('signing.')
        ->controller(\App\Http\Controllers\CommitteePortal\CommitteePortalSigningController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->name('index');

            Route::get('/{signingRequest}', 'show')
                ->whereNumber('signingRequest')
                ->name('show');

            Route::get('/{signingRequest}/preview', 'preview')
                ->whereNumber('signingRequest')
                ->name('preview');

            Route::post('/{signingRequest}/sign', 'sign')
                ->whereNumber('signingRequest')
                ->name('sign');

            Route::get('/{signingRequest}/download', 'download')
                ->whereNumber('signingRequest')
                ->name('download');
        });
        

    });
});

Route::middleware(['auth'])
    ->prefix('document-signing')
    ->name('document-signing.')
    ->group(function () {

        Route::get('/', [DocumentSigningController::class, 'index'])
            ->name('index');

        Route::get('/{signingRequest}', [DocumentSigningController::class, 'show'])
            ->name('show');

        Route::get('/{signingRequest}/preview', [DocumentSigningController::class, 'preview'])
            ->name('preview');

        Route::post('/{signingRequest}/sign', [DocumentSigningController::class, 'sign'])
            ->name('sign');

        Route::post('/{signingRequest}/reject', [DocumentSigningController::class, 'reject'])
            ->name('reject');

        Route::post('/{signingRequest}/revoke', [DocumentSigningController::class, 'revoke'])
            ->name('revoke'); 
            
        Route::get(
                '/{signingRequest}/download',
                [DocumentSigningController::class, 'download']
            )->name('download');    
    });


Route::middleware(['auth'])
    ->prefix('settings/organization')
    ->name('settings.organization.')
    ->group(function () {
        Route::get('/', [
            OrganizationSettingController::class,
            'edit'
        ])->name('edit');

        Route::put('/', [
            OrganizationSettingController::class,
            'update'
        ])->name('update');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CmsDashboardController::class, 'index'])
    ->name('dashboard');
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
   Route::resource('/barangays', BarangayController::class)->except(['create', 'show']);

    

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

        Route::get('/dashboard', [SecretaryDocumentController::class, 'dashboard'])
        ->middleware(['auth', 'role.check:secretary'])
    ->name('secretary.dashboard');

        Route::get('/notifications', [
        SecretaryNotificationController::class,
        'index'
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.notifications.index');

        Route::post('/notifications/{id}/read', [
            SecretaryNotificationController::class,
            'markAsRead'
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.notifications.read');

        Route::get('/documents', 
            [SecretaryDocumentController::class,'index']
        )->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.documents');
    
        Route::post('/documents/{id}/approve',
            [SecretaryDocumentController::class,'approve']
        ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.documents.approve');
    
        Route::post('/documents/{id}/reject',
            [SecretaryDocumentController::class,'reject']
        ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.documents.reject');

        Route::post('/documents/{id}/update-session',
            [SecretaryDocumentController::class, 'updateSession']
        ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.documents.updateSession');

        Route::resource('session-meetings', SessionMeetingController::class)
        ->middleware(['auth', 'role.check:secretary']);

        Route::get('/session/{id}/agenda',
        [SecretaryAgendaController::class,'index']
        ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.session.agenda');

        Route::post('/session/agenda/reorder',
            [SecretaryAgendaController::class,'reorder']
        ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.session.agenda.reorder');

        Route::get('/session/{id}/packet',
            [SecretaryAgendaController::class,'packet']
        )->name('secretary.session.packet');

        Route::get('/session/{id}/minutes-template',
            [SecretaryAgendaController::class,'minutesTemplate']
            ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.session.minutes');

        Route::get(
            '/session/{id}/minutes',
            [SecretaryMinutesController::class,'index']
            ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.minutes.index');
            
        Route::post(
            '/session/{id}/minutes',
            [SecretaryMinutesController::class,'store']
            )->middleware(['auth', 'role.check:secretary'])->name('secretary.minutes.store'); 
            
        Route::get(
            '/session/{id}/minutes-generate',
            [SecretaryMinutesController::class, 'generate']
            )->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.minutes.generate');
            
        Route::get(
            '/session/{id}/minutes-review',
            [SecretaryMinutesController::class,'review']
            )->name('secretary.minutes.review');  
            
        Route::get(
                '/session/{id}/remarks',
                [SecretaryRemarkController::class,'index']
                ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.remarks.index'); 
                
        Route::get('/session-meetings/{id}/attendance',
            [SessionMeetingController::class, 'attendance'])
             ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.session-meetings.attendance');

        Route::post('/session-meetings/{id}/attendance',
            [SessionMeetingController::class, 'saveAttendance'])
             ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.session-meetings.attendance.save'); 
            
        Route::delete(
            '/session-meetings/{sessionId}/attendance/{attendanceId}',
            [SessionMeetingController::class, 'deleteAttendance']
        ) ->middleware(['auth', 'role.check:secretary'])->name('secretary.session-meetings.attendance.delete');  
        
        Route::get('/session-meetings/{id}/control', [SessionControlController::class, 'show'])->middleware(['auth', 'role.check:secretary'])->name('secretary.session-control.show');
        Route::post('/session-meetings/{id}/control/start', [SessionControlController::class, 'start'])->middleware(['auth', 'role.check:secretary'])->name('secretary.session-control.start');
        Route::post(
            '/session-meetings/{id}/control/suspend',
            [SessionControlController::class, 'suspend']
        )->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.session-control.suspend');

        Route::post(
            '/session-meetings/{id}/control/resume',
            [SessionControlController::class, 'resume']
        )->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.session-control.resume');
        
        Route::post('/session-meetings/{id}/control/end', [SessionControlController::class, 'end'])->middleware(['auth', 'role.check:secretary'])->name('secretary.session-control.end');
       // Route::post('/session-meetings/{id}/control/agenda/{itemId}/activate', [SessionControlController::class, 'activateAgendaItem'])->name('secretary.session-control.activate');
        Route::post('/session-meetings/{id}/control/agenda/next', [SessionControlController::class, 'nextAgendaItem'])->middleware(['auth', 'role.check:secretary'])->name('secretary.session-control.next');
      //  Route::post('/session-meetings/{id}/control/agenda/{itemId}/disposition', [SessionControlController::class, 'recordDisposition'])->middleware(['auth', 'role.check:secretary'])->name('secretary.session-control.disposition');
        Route::post(
            '/session-meetings/{id}/control/agenda/{itemId}/activate',
            [SessionControlController::class, 'activateAgendaItem']
        )->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.session-control.activate');

        Route::post(
            '/session-meetings/{id}/control/agenda/{itemId}/disposition',
            [SessionControlController::class, 'recordDisposition']
        )->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.session-control.disposition');  

        Route::post(
            '/session-meetings/{id}/control/agenda/{itemId}/previous-minutes-decision',
            [SessionControlController::class, 'recordPreviousMinutesDecision']
        )->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.session-control.previous-minutes-decision');

        Route::post(
            '/session-meetings/{id}/control/previous-minutes',
            [SessionControlController::class, 'addPreviousMinutes']
        )->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.session-control.previous-minutes.add');    

        Route::get(
            '/session-meetings/{sessionId}/voting',
            [SessionVotingController::class, 'manage']
        )->middleware(['auth', 'role.check:secretary'])->name('secretary.votes.manage');

        Route::post(
            '/session/{id}/minutes/submit-review',
            [SecretaryMinutesController::class, 'submitForReview']
        )->name('secretary.minutes.submit-review');

        Route::get(
            '/minutes/review-results',
            [SecretaryMinutesController::class, 'reviewResults']
        )->middleware(['auth', 'role.check:secretary'])->name('minutes.review-results');

        Route::get('/minutes/returned', [
            SecretaryMinutesController::class,
            'returned'
        ])->name('minutes.returned');

        Route::post(
            '/session/{id}/minutes/finalize',
            [SecretaryMinutesController::class, 'finalize']
        )->middleware(['auth', 'role.check:secretary'])->name('secretary.minutes.finalize');

        Route::get(
            '/sessions/{session}/recordings',
            [SessionRecordingController::class, 'index']
        )->name('sessions.recordings.index');
        
        Route::post(
            '/sessions/{session}/recordings',
            [SessionRecordingController::class, 'store']
        )->middleware('auth')
            ->name('sessions.recordings.store');

        Route::get(
            '/sessions/{session}/recordings/{recording}/transcript',
            [SessionRecordingController::class, 'transcript']
        )->name('sessions.recordings.transcript');
        
        Route::post(
            '/session/{id}/minutes/generate-draft',
            [SecretaryMinutesController::class, 'generateDraft']
        )->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.minutes.generate-draft');

        Route::get('/committee-referrals', [
            CommitteeReferralController::class,
            'index',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.index');

        Route::get('/committee-referrals/{id}', [
            CommitteeReferralController::class,
            'show',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.show');

        Route::post('/committee-referrals/{id}/start-review', [
            CommitteeReferralController::class,
            'startReview',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.start-review');

        Route::post('/committee-referrals/{id}/submit-report', [
            CommitteeReferralController::class,
            'submitReport',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.submit-report');

        Route::get('/committee-referrals/{id}/report', [
            CommitteeReferralController::class,
            'downloadReport',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.report');

        Route::post('/committee-referrals/{id}/submit-for-signing', [
            CommitteeReferralController::class,
            'submitForSigning',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.submit-for-signing');

        Route::post('/committee-referrals/{id}/return-to-council', [
            CommitteeReferralController::class,
            'returnToCouncil',
        ])->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.return-to-council');

        Route::get(
            '/committee-referrals/{id}/add-to-agenda',
            [CommitteeReferralController::class, 'addToFutureAgenda']
        )->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.add-to-agenda');

        Route::post(
            '/committee-referrals/{id}/add-to-agenda',
            [CommitteeReferralController::class, 'storeInFutureAgenda']
        )->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-referrals.store-in-agenda');

        // Committee Meeting Management
        Route::get('/committee-meetings', [
            CommitteeMeetingController::class,
            'index',
        ])->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.index');

        Route::get('/committee-meetings/create', [
            CommitteeMeetingController::class,
            'create',
        ])->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.create');

        Route::post('/committee-meetings', [
            CommitteeMeetingController::class,
            'store',
        ])->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.store');

        Route::post('/committee-meetings/{id}/start', [
            CommitteeMeetingController::class,
            'startMeeting',
        ])->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.start');    

        Route::get('/committee-meetings/{id}', [
            CommitteeMeetingController::class,
            'show',
        ])->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.show');  
            
        Route::get('/committee-meetings/{id}/attendance', [
            CommitteeMeetingController::class,
            'attendance',
        ])->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.attendance'); 
            
        Route::post('/committee-meetings/{id}/attendance', [
            CommitteeMeetingController::class,
            'saveAttendance'
        ])
            ->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.attendance.save');  
            
        // Meeting Agenda Management
        Route::get('/committee-meetings/{id}/agenda', [
            CommitteeMeetingController::class,
            'manageAgenda',
        ])->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.agenda'); 
            
        Route::post('/committee-meetings/{id}/agenda', [
            CommitteeMeetingController::class,
            'addAgendaItem',
        ])->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.agenda.add');

        Route::patch('/committee-meetings/{id}/agenda/reorder', [
            CommitteeMeetingController::class,
            'reorderAgenda',
        ])->whereNumber('id')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.agenda.reorder');

        Route::delete('/committee-meetings/{id}/agenda/{itemId}', [
            CommitteeMeetingController::class,
            'removeAgendaItem',
        ])->whereNumber('id')
            ->whereNumber('itemId')
            ->middleware(['auth', 'role.check:secretary'])
            ->name('secretary.committee-meetings.agenda.remove'); 
            
        // Committee Meeting Deliberation
        Route::post(
            '/committee-meetings/{id}/agenda/{itemId}/deliberations',
            [CommitteeMeetingController::class, 'storeDeliberation']
        )->whereNumber('id')
        ->whereNumber('itemId')
        ->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-meetings.deliberations.store');  
        
        // Start deliberation
        Route::post(
            '/committee-meetings/{id}/agenda/{itemId}/start',
            [CommitteeMeetingController::class, 'startDeliberation']
        )->whereNumber('id')
        ->whereNumber('itemId')
        ->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-meetings.deliberations.start');

        // Finalize deliberation
        Route::post(
            '/committee-meetings/{id}/agenda/{itemId}/finalize',
            [CommitteeMeetingController::class, 'finalizeDeliberation']
        )->whereNumber('id')
        ->whereNumber('itemId')
        ->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-meetings.deliberations.finalize');

        // Close Committee Meeting
        Route::post(
            '/committee-meetings/{id}/close',
            [CommitteeMeetingController::class, 'closeMeeting']
        )->whereNumber('id')
        ->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-meetings.close');

        Route::get(
            '/committee-meetings/{id}/deliberation-report',
            [CommitteeMeetingController::class, 'downloadDeliberationReport']
        )->whereNumber('id')
            ->name('secretary.committee-meetings.report');

        Route::post(
        '/committee-meetings/{id}/upload-consolidated-report',
        [CommitteeMeetingController::class, 'uploadMeetingReport']
    )->whereNumber('id')
        ->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-meetings.report.upload');

    Route::get(
        '/committee-meetings/{id}/consolidated-report',
        [CommitteeMeetingController::class, 'downloadMeetingReport']
    )->whereNumber('id')
        ->middleware(['auth', 'role.check:secretary'])
        ->name('secretary.committee-meetings.report-file');  
        
    Route::get(
        '/votes/{voteId}/progress',
        [SessionVotingController::class, 'progress']
    )->name('secretary.votes.progress');

    Route::get(
        '/votes/{voteId}/report',
        [SessionVotingController::class, 'report']
    )->name('secretary.votes.report');

    Route::post(
        '/votes/{voteId}/submit-for-signing',
        [SessionVotingController::class, 'submitForSigning']
    )->middleware(['auth', 'role.check:secretary'])
    ->name('secretary.votes.submit-for-signing');
        
        // routes/web.php

    Route::get('/settings/live-session', [\App\Http\Controllers\LiveSessionController::class, 'editLive'])
        ->name('settings.live.edit');
    Route::post('/settings/live-session', [\App\Http\Controllers\LiveSessionController::class, 'updateLive'])
        ->name('settings.live.update');

    
    });

});

Route::middleware(['auth'])
    ->prefix('presiding-officer')
    ->name('presiding-officer.')
    ->group(function () {
        // Presiding Officer Live Deliberation Workspace
        Route::middleware(['role.check:presiding_officer'])->group(function () {

            Route::get(
                '/dashboard',
                [PresidingOfficerMinutesController::class, 'minutesdashboard']
           )->name('dashboard');

            Route::get(
                '/minutes',
                [PresidingOfficerMinutesController::class, 'index']
            )->name('minutes.index');

            Route::post(
                '/minutes/{id}/mark-reviewed',
                [PresidingOfficerMinutesController::class, 'markReviewed']
            )->name('minutes.mark-reviewed');

            Route::post(
                '/minutes/{id}/return-for-corrections',
                [PresidingOfficerMinutesController::class, 'returnForCorrections']
            )->name('minutes.return-for-corrections');

        
            Route::get('/sessions', [LiveDeliberationController::class,'index'
                ])->name('sessions.index');

            Route::get('/sessions/{sessionId}', [LiveDeliberationController::class,'show'
                ])->whereNumber('sessionId')->name('sessions.show');

            Route::get(
                '/sessions/{sessionId}/agenda/{agendaType}/{agendaItemId}/preview',
                [LiveDeliberationController::class, 'previewDocument']
            )
                ->whereNumber('sessionId')
                ->whereIn('agendaType', ['document', 'committee_report', 'previous_minutes'])
                ->whereNumber('agendaItemId')
                ->name('sessions.agenda.preview');   
                
            
            Route::post(
                '/sessions/{sessionId}/agenda/{agendaType}/{agendaItemId}/proceedings',
                [LiveDeliberationController::class, 'storeProceeding']
            )
                ->whereNumber('sessionId')
                ->whereIn('agendaType', ['document', 'committee_report', 'previous_minutes'])
                ->whereNumber('agendaItemId')
                ->name('sessions.agenda.proceedings.store');
    
        });
    });



Route::middleware(['auth'])
    ->prefix('secretary')
    ->group(function () {
        Route::post(
            '/session-meetings/{sessionId}/votes',
            [SessionVotingController::class, 'open']
        )->name('secretary.votes.open');

        Route::post(
            '/votes/{voteId}/close',
            [SessionVotingController::class, 'close']
        )->name('secretary.votes.close');

        Route::post(
            '/votes/{voteId}/finalize',
            [SessionVotingController::class, 'finalize']
        )->name('secretary.votes.finalize');
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