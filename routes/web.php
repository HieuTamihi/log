<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
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
    
    // Language switching
    Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');
    
    // Main Graph View (Dashboard)
    Route::get('/', [GraphController::class, 'index'])->name('dashboard');
    
    // Folders API
    Route::get('/api/folders', [FolderController::class, 'index'])->name('folders.index');
    Route::get('/api/folders/tree', [FolderController::class, 'tree'])->name('folders.tree');
    Route::get('/api/folders/{folder}', [FolderController::class, 'show'])->name('folders.show');
    Route::post('/api/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('/api/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/api/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
    
    // Notes API
    Route::get('/api/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/api/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/api/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::put('/api/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/api/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    
    // Users API
    Route::get('/api/users', [UserController::class, 'index'])->name('users.index');

    // Audit Logs
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit_logs.index');

    // Graph/Cards API
    Route::post('/api/cards', [GraphController::class, 'createCard'])->name('cards.create');
    Route::post('/api/cards/folder', [GraphController::class, 'createFolderCard'])->name('cards.folder.create');
    Route::post('/api/cards/{card}/position', [GraphController::class, 'updatePosition'])->name('cards.position');
    Route::post('/api/cards/{card}/links', [GraphController::class, 'addLink'])->name('cards.links.add');
    Route::delete('/api/cards/{card}/links', [GraphController::class, 'removeLink'])->name('cards.links.remove');
    Route::delete('/api/cards/{card}', [GraphController::class, 'deleteCard'])->name('cards.destroy');
});
