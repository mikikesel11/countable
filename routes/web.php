<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountController;
use App\Http\Controllers\HabitController;

Route::view('/', 'welcome');

Route::get('habits/{habit}/counts', [CountController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('counts');
    
Route::get('habits', [HabitController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('habits');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
