<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineManagementController;
use App\Http\Controllers\SubsystemController;
use App\Http\Controllers\SubsystemManagementController;
use App\Http\Controllers\ComponentManagementController;
use App\Http\Controllers\UpgradeController;
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
    
    // Language switching
    Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');
    
    // Search
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');
    
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // System Sight - Business Machine Dashboard
    Route::get('/', [MachineController::class, 'index'])->name('dashboard');
    Route::get('/machines/{slug}', [MachineController::class, 'show'])->name('machines.show');
    Route::get('/machines/{machineSlug}/{subsystemSlug}', [SubsystemController::class, 'show'])->name('subsystems.show');
    
    // Machine Management (CRUD)
    Route::get('/manage/machines', [MachineManagementController::class, 'index'])->name('machines.index');
    Route::get('/manage/machines/create', [MachineManagementController::class, 'create'])->name('machines.create');
    Route::post('/manage/machines', [MachineManagementController::class, 'store'])->name('machines.store');
    Route::get('/manage/machines/{machine}/edit', [MachineManagementController::class, 'edit'])->name('machines.edit');
    Route::put('/manage/machines/{machine}', [MachineManagementController::class, 'update'])->name('machines.update');
    Route::delete('/manage/machines/{machine}', [MachineManagementController::class, 'destroy'])->name('machines.destroy');
    
    // Subsystem Management (CRUD)
    Route::get('/manage/machines/{machine}/subsystems/create', [SubsystemManagementController::class, 'create'])->name('subsystems.create');
    Route::post('/manage/machines/{machine}/subsystems', [SubsystemManagementController::class, 'store'])->name('subsystems.store');
    Route::get('/manage/subsystems/{subsystem}/edit', [SubsystemManagementController::class, 'edit'])->name('subsystems.edit');
    Route::put('/manage/subsystems/{subsystem}', [SubsystemManagementController::class, 'update'])->name('subsystems.update');
    Route::delete('/manage/subsystems/{subsystem}', [SubsystemManagementController::class, 'destroy'])->name('subsystems.destroy');
    Route::patch('/manage/machines/{machine}/status', [MachineManagementController::class, 'updateStatus'])->name('machines.status.update');
    Route::patch('/machines/{machine}/coordinates', [MachineController::class, 'updateCoordinates'])->name('machines.coordinates.update');
    Route::post('/machines/swap-order', [MachineController::class, 'swapOrder'])->name('machines.order.swap');
    Route::patch('/manage/subsystems/{subsystem}/status', [SubsystemManagementController::class, 'updateStatus'])->name('subsystems.status.update');
    
    // Component Management (CRUD)
    Route::get('/manage/subsystems/{subsystem}/components/create', [ComponentManagementController::class, 'create'])->name('components.create');
    Route::post('/manage/subsystems/{subsystem}/components', [ComponentManagementController::class, 'store'])->name('components.store');
    Route::get('/manage/components/{component}/edit', [ComponentManagementController::class, 'edit'])->name('components.edit');
    Route::put('/manage/components/{component}', [ComponentManagementController::class, 'update'])->name('components.update');
    Route::patch('/manage/components/{component}/status', [ComponentManagementController::class, 'updateStatus'])->name('components.updateStatus');
    Route::delete('/manage/components/{component}', [ComponentManagementController::class, 'destroy'])->name('components.destroy');
    
    // Machine Connections
    Route::get('/machine-connections', [App\Http\Controllers\MachineConnectionController::class, 'index'])->name('connections.index');
    Route::post('/machine-connections', [App\Http\Controllers\MachineConnectionController::class, 'store'])->name('connections.store');
    Route::delete('/machine-connections/{connection}', [App\Http\Controllers\MachineConnectionController::class, 'destroy'])->name('connections.destroy');
    Route::post('/machine-connections/cleanup', [App\Http\Controllers\MachineConnectionController::class, 'cleanup'])->name('connections.cleanup');
    
    // Upgrades
    Route::get('/components/{componentId}/upgrades/create', [UpgradeController::class, 'create'])->name('upgrades.create');
    Route::post('/upgrades', [UpgradeController::class, 'store'])->name('upgrades.store');
    Route::get('/upgrades/{upgrade}/edit', [UpgradeController::class, 'edit'])->name('upgrades.edit');
    Route::put('/upgrades/{upgrade}', [UpgradeController::class, 'update'])->name('upgrades.update');
    Route::post('/upgrades/{upgrade}/update', [UpgradeController::class, 'update'])->name('upgrades.update_post'); // Fallback for 404 issue
    Route::post('/upgrades/{upgrade}/ship', [UpgradeController::class, 'ship'])->name('upgrades.ship');
    Route::delete('/upgrades/{upgrade}', [UpgradeController::class, 'destroy'])->name('upgrades.destroy');
    
    // Old routes (for backward compatibility)
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs', [LogController::class, 'store'])->name('logs.store');
    Route::put('/logs/{log}', [LogController::class, 'update'])->name('logs.update');
    Route::delete('/logs/{log}', [LogController::class, 'destroy'])->name('logs.destroy');
    
    Route::get('/solutions/create', [SolutionController::class, 'create'])->name('solutions.create');
    Route::post('/solutions', [SolutionController::class, 'store'])->name('solutions.store');
    Route::get('/solutions/{solution}', [SolutionController::class, 'show'])->name('solutions.show');
    Route::put('/solutions/{solution}', [SolutionController::class, 'update'])->name('solutions.update');
    
    // Help page
    Route::get('/help', function () {
        return view('help.index');
    })->name('help');
    
    // Seed demo data route (temporary)
    Route::get('/seed-demo', function() {
        $component = \App\Models\Component::first();
        if ($component) {
            $component->update([
                'metric_value' => 83,
                'metric_label' => 'Leads',
                'current_issue' => 'Cần cải thiện tỷ lệ chuyển đổi'
            ]);
            
            // Create a sample upgrade if none exists
            if (\App\Models\Upgrade::count() === 0) {
                \App\Models\Upgrade::create([
                    'component_id' => $component->id,
                    'name' => 'Cải thiện quy trình viết hook',
                    'purpose' => 'Tăng tỷ lệ chuyển đổi từ 10% lên 15%',
                    'trigger' => 'Khi viết content mới',
                    'steps' => ['Phân tích đối tượng', 'Viết 3 hook variations', 'A/B test'],
                    'definition_of_done' => 'Tỷ lệ chuyển đổi >= 15%',
                    'status' => 'active'
                ]);
            }
            
            return redirect('/')->with('success', 'Demo data đã được thêm! Refresh trang để xem thay đổi.');
        }
        return redirect('/')->with('error', 'Không tìm thấy component. Vui lòng tạo một machine/subsystem/component trước.');
    })->name('seed.demo');
});
