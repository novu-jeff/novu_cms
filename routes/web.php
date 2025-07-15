<?php

use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth::routes();
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('/members', MemberController::class)->except('create', 'show');
    Route::resource('/standing-committee', CommitteeController::class)->except('create', 'show');
});