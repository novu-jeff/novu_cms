<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\SessionVotingController;
use App\Http\Controllers\Api\OrganizationSettingsApiController;
use App\Http\Controllers\Api\LisNotificationController;

// Organization Settings API
Route::get(
    '/organization-settings',
    [OrganizationSettingsApiController::class, 'show']
);

Route::post(
    '/internal/lis/document-submitted',
    [LisNotificationController::class, 'documentSubmitted']
);

Route::post('/member/login', [
    MemberAuthController::class,
    'login',
])->middleware('throttle:5,1');

// Member authentication and ballot casting
Route::middleware([
    'auth:sanctum',
    'abilities:vote:cast',
])->group(function () {
    Route::post('/member/logout', [
        MemberAuthController::class,
        'logout',
    ]);

    Route::post('/votes/{voteId}/cast', [
        SessionVotingController::class,
        'cast',
    ]);

    Route::get('/member/active-vote', [
        SessionVotingController::class,
        'activeVote',
    ]);

    Route::get(
        '/member/voting-history',
        [SessionVotingController::class, 'votingHistory']
    );
});

// Separate permission for authorized session officials
Route::middleware([
    'auth:sanctum',
    'abilities:vote:manage',
])->group(function () {
    Route::post('/sessions/{sessionId}/votes', [
        SessionVotingController::class,
        'open',
    ]);
});
