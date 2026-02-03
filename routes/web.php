<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SolutionController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs', [LogController::class, 'store'])->name('logs.store');
    Route::put('/logs/{log}', [LogController::class, 'update'])->name('logs.update');
    Route::delete('/logs/{log}', [LogController::class, 'destroy'])->name('logs.destroy');
    
    // Solutions
    Route::get('/solutions/create', [SolutionController::class, 'create'])->name('solutions.create');
    Route::post('/solutions', [SolutionController::class, 'store'])->name('solutions.store');
    Route::get('/solutions/{solution}', [SolutionController::class, 'show'])->name('solutions.show');
    Route::put('/solutions/{solution}', [SolutionController::class, 'update'])->name('solutions.update');
});
