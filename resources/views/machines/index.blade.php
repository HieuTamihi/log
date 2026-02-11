@extends('layouts.app')

@section('title', 'System Sight - Business Machine')

@section('content')
<div class="ss-wrapper">
    <!-- Animated Background -->
    <div class="bg-gradient"></div>
    
    <!-- Navbar Component -->
    <x-navbar />
    
    <!-- Floating Action Button -->
    <a href="{{ route('machines.create') }}" class="fab-btn" title="Create Machine">
        <i class="fas fa-plus"></i>
    </a>

    <main class="ss-main">
        <div class="ss-container">
            <!-- Top Section: Title + Streak -->
            <div class="top-section">
                <div class="page-header">
                    <h1 class="page-title">{{ __('messages.business_machine') }}</h1>
                    <p class="page-subtitle">{{ __('messages.see_build_improve') }}</p>
                </div>
            </div>

            <!-- Zoom Controls -->
            @if($machines->count() > 0)
            <div class="zoom-controls-wrapper">
                <div class="zoom-controls-row">
                    <button onclick="toggleZoomView()" class="zoom-toggle-btn" id="zoomToggleBtn">
                        <i class="fas fa-search-minus" id="zoomIcon"></i>
                        <span id="zoomText">{{ __('messages.overview') }}</span>
                    </button>

                </div>
                <div class="zoom-hint">
                    <i class="fas fa-info-circle"></i>
                    <span>Hold Ctrl/Cmd + Scroll to zoom</span>
                </div>
            </div>

            <!-- Zoom Level Indicator -->
            <div class="zoom-level-indicator" id="zoomLevelIndicator">
                Zoom: <span id="zoomPercentage">100</span>%
            </div>
            @endif

            <!-- Machine Flow Visualization -->
            <div class="machine-flow-wrapper" id="machineFlowWrapper">
                <!-- Connection Canvas -->
                <canvas id="connectionCanvas" class="connection-canvas"></canvas>
                
                <div class="machine-flow" id="machineFlow">
                    @foreach($machines as $index => $machine)
                    <div class="machine-card" 
                         data-machine-id="{{ $machine->id }}"
                         style="animation-delay: {{ $index * 0.15 }}s; cursor: pointer;"
                         onclick="window.location.href='{{ route('machines.show', $machine->slug) }}'">
                        <!-- Card Glow Effect -->
                        <div class="card-glow"></div>

                        <!-- Menu 3 chấm -->
                        <div class="card-menu-wrapper">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event, {{ $machine->id }})">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="card-menu-dropdown" id="cardMenu{{ $machine->id }}">
                                <a href="{{ route('machines.edit', $machine) }}" class="card-menu-item edit">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                <form action="{{ route('machines.destroy', $machine) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this machine?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="card-menu-item danger">
                                        <i class="fas fa-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>


                        <h3 class="machine-name">{{ $machine->name }}</h3>
                        @if($machine->sub_header || $machine->description)
                            <div class="machine-sub-header" style="font-size: 14px; color: #6366f1; margin-bottom: 4px; font-weight: 500;">{{ $machine->sub_header ?? $machine->description }}</div>
                        @endif
                        <p class="machine-description">{{ $machine->detail_description }}</p>

                        <!-- Machine Metrics -->
                        <div class="machine-metrics">
                            @php
                                $status = $machine->health_status;
                                $icon = $machine->status_icon;
                                $colorClass = match($status) {
                                    'green', 'smooth' => 'success',
                                    'red', 'on_fire' => 'danger',
                                    'yellow', 'needs_love' => 'warning',
                                    default => 'secondary'
                                };
                                $statusText = match($status) {
                                    'green', 'smooth' => 'Smooth',
                                    'red', 'on_fire' => 'On Fire',
                                    'yellow', 'needs_love' => 'Needs Love',
                                    default => 'Unknown'
                                };
                            @endphp
                            
                            <div id="machine-status-badge-{{ $machine->id }}" class="metric-badge {{ $colorClass }}">
                                <span class="metric-icon">{{ $icon }}</span>
                            </div>
                        </div>

                        <!-- Subsystems (visible when zoomed out) -->
                        <div class="subsystems-container">
                            @foreach($machine->subsystems as $subsystem)
                            <a href="{{ route('subsystems.show', [$machine->slug, $subsystem->slug]) }}" class="subsystem-node" onclick="event.stopPropagation()">
                                <span class="subsystem-icon">{{ $subsystem->icon ?? '📦' }}</span>
                                <span class="subsystem-name">{{ $subsystem->name }}</span>
                                <span class="subsystem-health health-{{ $subsystem->health_status }}"></span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Machine Context Menu -->
<div id="machineContextMenu" class="context-menu">
    <div class="context-menu-item" onclick="updateMachineStatus('green')">
        <span class="status-dot dot-green"></span>
        <span>Green</span>
    </div>
    <div class="context-menu-item" onclick="updateMachineStatus('yellow')">
        <span class="status-dot dot-yellow"></span>
        <span>Yellow</span>
    </div>
    <div class="context-menu-item" onclick="updateMachineStatus('red')">
        <span class="status-dot dot-red"></span>
        <span>Red</span>
    </div>
     <div class="context-menu-separator"></div>
    <div class="context-menu-item" onclick="updateMachineStatus('auto')">
        <span class="status-dot dot-auto">⚙️</span>
        <span>Auto</span>
    </div>
</div>



@push('styles')
<style>
    /* Additions for subsystem node health colors */
    .subsystem-health.health-green, .subsystem-health.health-smooth { background-color: #10b981; }
    .subsystem-health.health-red, .subsystem-health.health-on_fire { background-color: #ef4444; }
    .subsystem-health.health-yellow, .subsystem-health.health-needs_love { background-color: #fbbf24; }

    /* Context Menu */
    .context-menu {
        display: none;
        position: fixed;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        min-width: 160px;
        overflow: hidden;
        padding: 4px;
    }
    .context-menu.show { display: block; animation: fadeIn 0.1s ease-out; }
    .context-menu-item {
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        border-radius: 8px;
        transition: background 0.2s;
        font-weight: 500;
        color: #475569;
    }
    .context-menu-item:hover { background: #f1f5f9; color: #1a202c; }
    .context-menu-separator { height: 1px; background: #e2e8f0; margin: 4px 0; }
    .status-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
    .dot-green { background: #10b981; }
    .dot-yellow { background: #fbbf24; }
    .dot-red { background: #ef4444; }
    .dot-auto { background: none; font-size: 12px; display: flex; align-items: center; justify-content: center; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #fafbfc;
        color: #1a202c;
        line-height: 1.6;
    }

    .bg-gradient {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.05) 0%, transparent 50%);
        z-index: -1;
    }

    .ss-wrapper {
        min-height: 100vh;
    }

    .ss-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 32px;
    }

    /* ===== HEADER ===== */
    .ss-header {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        position: sticky;
        top: 0;
        z-index: 100;
        padding: 16px 0;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 18px;
        color: #1a202c;
    }

    .logo-text {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }

    .search-box {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 16px;
    }

    .search-input {
        padding: 9px 12px 9px 38px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        width: 240px;
        font-size: 14px;
        background: white;
        color: #1a202c;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .search-input::placeholder {
        color: #cbd5e1;
    }

    .language-switcher {
        display: flex;
        align-items: center;
    }

    .lang-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
    }

    .lang-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
        color: #6366f1;
    }

    .lang-btn .flag {
        font-size: 18px;
        line-height: 1;
    }

    .lang-btn .lang-text {
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .icon-btn {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s;
        position: relative;
    }

    .icon-btn i {
        font-size: 18px;
    }

    .icon-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #6366f1;
    }

    .notification-wrapper {
        position: relative;
    }

    /* Floating Action Button */
    .fab-btn {
        position: fixed;
        bottom: 32px;
        right: 32px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border: none;
        border-radius: 16px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .fab-btn i {
        font-size: 24px;
    }

    .fab-btn:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.5);
        background: linear-gradient(135deg, #5254cc 0%, #9333ea 100%);
    }

    .fab-btn:active {
        transform: scale(0.95);
    }

    .notification-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }

    .notification-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 380px;
        max-height: 500px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        display: none;
        flex-direction: column;
        z-index: 1000;
    }

    .notification-dropdown.show {
        display: flex;
    }

    .notification-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .notification-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1a202c;
    }

    .btn-mark-all-read {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-mark-all-read i {
        font-size: 12px;
    }

    .btn-mark-all-read:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #6366f1;
    }

    .notification-list {
        overflow-y: auto;
        max-height: 420px;
    }

    .notification-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 40px 20px;
        color: #94a3b8;
        font-size: 14px;
    }

    .notification-loading i {
        font-size: 20px;
    }

    .notification-item {
        display: flex;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }

    .notification-item:hover {
        background: #f8fafc;
    }

    .notification-item.unread {
        background: #f0f9ff;
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #3b82f6;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notification-icon i {
        font-size: 18px;
    }

    .notification-icon.success {
        background: #d1fae5;
        color: #065f46;
    }

    .notification-icon.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .notification-icon.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .notification-icon.info {
        background: #dbeafe;
        color: #1e40af;
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        font-size: 14px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 2px;
    }

    .notification-message {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .notification-time {
        font-size: 12px;
        color: #94a3b8;
    }

    .notification-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .notification-empty i {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .notification-empty p {
        font-size: 14px;
    }

    .user-menu {
        position: relative;
        padding: 3px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .user-menu:hover {
        border-color: #cbd5e1;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
    }

    /* ===== USER DROPDOWN ===== */
    .user-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 160px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s;
        z-index: 1000;
        display: none;
        padding: 4px;
    }

    .user-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        display: block;
    }

    .dropdown-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: none;
        border: none;
        text-align: left;
        font-size: 14px;
        color: #ef4444;
        cursor: pointer;
        transition: all 0.2s;
        border-radius: 8px;
        font-weight: 500;
    }

    .dropdown-item i {
        font-size: 14px;
    }

    .dropdown-item:hover {
        background: #fef2f2;
    }

    /* ===== MAIN CONTENT ===== */
    .ss-main {
        padding: 40px 0 80px;
    }

    .top-section {
        text-align: center;
        margin-bottom: 36px;
    }

    .page-header {
        margin: 0 auto;
    }

    .page-title {
        font-size: 48px;
        font-weight: 700;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 18px;
        font-weight: 400;
    }

    .streak-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #92400e;
        box-shadow: 0 2px 8px rgba(251, 191, 36, 0.15);
        margin-top: 16px;
    }

    .streak-icon {
        font-size: 18px;
    }

    /* ===== MAIN ACTION ===== */
    .main-action {
        text-align: center;
        margin-bottom: 48px;
    }

    .quick-ship-wrapper {
        position: relative;
        display: inline-block;
    }

    .btn-ship {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .btn-ship::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-ship:hover::before {
        left: 100%;
    }

    .btn-ship i {
        font-size: 16px;
        transition: transform 0.3s;
    }

    .btn-ship:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    .btn-ship:hover i:first-child {
        transform: scale(1.1) rotate(-10deg);
    }

    .btn-ship:hover i:last-child {
        transform: rotate(180deg);
    }

    .btn-ship:active {
        transform: translateY(0);
    }

    .quick-ship-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        left: 50%;
        transform: translateX(-50%);
        width: 380px;
        max-height: 420px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        display: none;
        flex-direction: column;
        z-index: 1000;
        overflow: hidden;
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    .quick-ship-dropdown.show {
        display: flex;
    }

    .quick-ship-header {
        padding: 20px 20px 16px;
        border-bottom: 1px solid #f1f5f9;
        background: white;
    }

    .quick-ship-header h4 {
        font-size: 16px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .quick-ship-header p {
        font-size: 13px;
        color: #94a3b8;
    }

    .quick-ship-list {
        overflow-y: auto;
        max-height: 340px;
    }

    .quick-ship-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        border-bottom: 1px solid #f8fafc;
        text-decoration: none;
        transition: all 0.2s;
        position: relative;
    }

    .quick-ship-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 0;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        transition: width 0.2s;
    }

    .quick-ship-item:hover {
        background: #fafbfc;
    }

    .quick-ship-item:hover::before {
        width: 3px;
    }

    .quick-ship-item:last-child {
        border-bottom: none;
    }

    .quick-ship-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .quick-ship-icon i {
        font-size: 18px;
    }

    .quick-ship-icon.danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }

    .quick-ship-icon.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
    }

    .quick-ship-info {
        flex: 1;
        min-width: 0;
    }

    .quick-ship-name {
        font-size: 14px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 3px;
    }

    .quick-ship-path {
        font-size: 12px;
        color: #94a3b8;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .quick-ship-arrow {
        font-size: 12px;
        color: #cbd5e1;
        transition: all 0.2s;
    }

    .quick-ship-item:hover .quick-ship-arrow {
        color: #6366f1;
        transform: translateX(3px);
    }

    .quick-ship-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        text-align: center;
    }

    .quick-ship-empty i {
        font-size: 40px;
        color: #10b981;
        margin-bottom: 12px;
        opacity: 0.8;
    }

    .quick-ship-empty p {
        font-size: 14px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 6px;
    }

    .quick-ship-empty small {
        font-size: 12px;
        color: #94a3b8;
    }

    /* ===== ZOOM CONTROLS ===== */
    .zoom-controls-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-bottom: 32px;
    }

    .zoom-controls-row {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .zoom-toggle-btn, .connection-mode-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .zoom-toggle-btn:hover, .connection-mode-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
        color: #6366f1;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    }

    .connection-mode-btn.active {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-color: transparent;
    }

    .zoom-toggle-btn i, .connection-mode-btn i {
        font-size: 16px;
    }

    /* Connection Canvas */
    .connection-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 5;
        transform-origin: top center;
    }

    /* Machine cards in connection mode */
    .connection-mode .machine-card {
        cursor: crosshair;
        position: relative;
        z-index: 10;
    }

    .connection-mode .machine-card:hover {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
    }

    .connection-mode .machine-card.connecting {
        box-shadow: 0 0 0 3px #6366f1;
    }

    /* Ensure machine flow has proper stacking */
    .machine-flow {
        position: relative;
        z-index: 10;
    }

    .zoom-hint {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #94a3b8;
        padding: 6px 12px;
        background: rgba(148, 163, 184, 0.1);
        border-radius: 6px;
    }

    .zoom-hint i {
        font-size: 12px;
    }

    /* Zoom level indicator */
    .zoom-level-indicator {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
        z-index: 1000;
    }

    .zoom-level-indicator.show {
        opacity: 1;
    }

    /* ===== SUBSYSTEMS CONTAINER ===== */
    .subsystems-container {
        display: none;
        flex-direction: column;
        gap: 8px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
    }

    .subsystem-node {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: rgba(99, 102, 241, 0.05);
        border: 1px solid rgba(99, 102, 241, 0.1);
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
    }

    .subsystem-node::before {
        content: '';
        position: absolute;
        left: -20px;
        top: 50%;
        width: 20px;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.3));
    }

    .subsystem-node:hover {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.3);
        transform: translateX(4px);
    }

    .subsystem-icon {
        font-size: 16px;
        line-height: 1;
    }

    .subsystem-name {
        flex: 1;
        font-size: 13px;
        font-weight: 500;
        color: #1a202c;
    }

    .subsystem-health {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .subsystem-health.health-smooth {
        background: #10b981;
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
    }

    .subsystem-health.health-needs_love {
        background: #f59e0b;
        box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
    }

    .subsystem-health.health-on_fire {
        background: #ef4444;
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
        animation: pulse 2s infinite;
    }

    /* ===== ZOOM OUT VIEW ===== */
    .machine-flow-wrapper.zoomed-out {
        perspective: 1000px;
    }

    /* Show subsystems when zoomed out */
    .machine-flow.overview-mode .subsystems-container {
        display: flex;
    }

    .machine-flow.overview-mode .machine-metrics {
        display: none;
    }

    .machine-flow.overview-mode .machine-card {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .machine-flow.overview-mode .machine-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    /* ===== MACHINE FLOW ===== */
    .machine-flow-wrapper {
        margin-bottom: 56px;
        transition: all 0.4s ease;
        overflow: visible;
        position: relative;
        min-height: 500px;
    }

    .machine-flow {
        position: relative;
        width: 100%;
        min-height: 80vh;
        padding: 40px;
        display: flex;
        flex-wrap: wrap;
        gap: 80px; /* Space for arrows */
        align-items: flex-start;
        justify-content: center;
    }

    @media (min-width: 1200px) {
        .machine-flow {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .machine-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        position: relative; /* Relative for internal elements */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        transition: all 0.3s;
        min-width: 350px;
        width: 350px;
        display: flex;
        flex-direction: column;
        z-index: 2; /* Above canvas */
    }

    .machine-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .card-glow {
        display: none;
    }

    .machine-header {
        margin-bottom: 20px;
    }

    .machine-icon-wrapper {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
    }

    .machine-icon {
        font-size: 32px;
    }

    .machine-name {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #1a202c;
    }

    .machine-description {
        color: #64748b;
        font-size: 15px;
        margin-bottom: 20px;
    }

    .machine-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
    }

    .metric-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .metric-badge.success {
        background: #d1fae5;
        color: #065f46;
    }

    .metric-badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .metric-badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .metric-count {
        padding: 6px 12px;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }

    /* Machine Metric Display (like "83 Leads") */
    .machine-metric-display {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 12px;
    }

    .machine-metric-display .metric-value {
        font-size: 28px;
        font-weight: 700;
        color: #1a202c;
    }

    .machine-metric-display .metric-label {
        font-size: 16px;
        font-weight: 500;
        color: #64748b;
    }

    /* Machine Current Issue */
    .machine-issue {
        font-size: 14px;
        color: #94a3b8;
        margin-bottom: 20px;
        font-style: italic;
    }

    .btn-upgrade {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        transition: all 0.2s;
    }

    .btn-upgrade i {
        font-size: 14px;
    }

    .btn-upgrade:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .machine-card {
        position: relative;
    }

    /* ===== NEXT UPGRADES ===== */
    .next-upgrades {
        margin-top: 56px;
    }

    .section-title {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #1a202c;
    }

    .upgrade-chips {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chip:hover {
        border-color: #6366f1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .chip-icon {
        font-size: 16px;
    }

    .chip-add {
        width: 44px;
        padding: 10px;
        justify-content: center;
        background: #f8fafc;
    }

    .chip-add:hover {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border-color: #6366f1;
        color: white;
    }

    /* ===== CARD MENU ===== */
    .card-menu-wrapper {
        position: absolute;
        top: 16px;
        right: 16px;
        z-index: 10;
    }

    .card-menu-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s;
    }

    .card-menu-btn:hover {
        background: white;
        border-color: #cbd5e1;
        color: #6366f1;
    }

    .card-menu-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        min-width: 140px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        display: none;
        flex-direction: column;
        padding: 6px;
        z-index: 100;
    }

    .card-menu-dropdown.show {
        display: flex;
    }

    .card-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: none;
        border: 1px solid transparent;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.15s;
        width: 100%;
        text-align: left;
    }

    .card-menu-item i {
        font-size: 14px;
        width: 16px;
        text-align: center;
    }

    .card-menu-item:hover {
        background: #f8fafc;
        color: #6366f1;
    }

    .card-menu-item.danger {
        color: #ef4444;
    }

    .card-menu-item.danger:hover {
        background: #fef2f2;
        border-color: #fca5a5;
    }

    .card-menu-item.edit {
        color: #0f766e;
    }

    .card-menu-item.edit:hover {
        background: #f0fdfa;
        border-color: #5eead4;
        color: #0d9488;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .page-title {
            font-size: 40px;
        }
    }

    @media (max-width: 768px) {
        .ss-container {
            padding: 0 20px;
        }
        .page-title {
            font-size: 32px;
        }
        .page-subtitle {
            font-size: 16px;
        }
        .search-input {
            width: 160px;
        }
        .machine-flow {
            grid-template-columns: 1fr;
        }
        .machine-card {
            padding: 24px;
        }
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
</style>
@endpush

@push('scripts')
<script>
    // Ordered Flow Logic
    let machines = []; // Will be populated from DOM
    let canvas, ctx;
    let resizeObserver;

    // Load notification count on page load
    window.addEventListener('load', function() {
        initializeFlow();
    });

    function initializeFlow() {
        console.log('Initializing ordered flow...');
        
        // Notification logic
        fetch('/notifications')
            .then(res => res.json())
            .then(data => updateNotificationBadge(data.unread_count))
            .catch(err => console.error('Error loading notification count:', err));

        // Setup canvas
        canvas = document.getElementById('connectionCanvas');
        if (canvas) {
            ctx = canvas.getContext('2d');
            window.addEventListener('resize', updateLayout);
            
            // Use ResizeObserver for more robust updates
            resizeObserver = new ResizeObserver(() => {
                updateLayout();
            });
            resizeObserver.observe(document.getElementById('machineFlow'));
            
            // Initial draw
            setTimeout(updateLayout, 100);
        }
    }

        function updateLayout() {
        if (!canvas || !ctx) return;

        const container = document.getElementById('machineFlow');
        if (!container) return;

        // Resize canvas to match container content width/height
        // Using scrollWidth/Height ensures we cover if content overflows
        canvas.width = container.scrollWidth;
        canvas.height = container.scrollHeight;
        
        drawOrderedConnections();
    }

        function drawOrderedConnections() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Remove existing swap buttons
        document.querySelectorAll('.swap-btn').forEach(btn => btn.remove());

        const cards = Array.from(document.querySelectorAll('.machine-card'));
        if (cards.length < 2) return;

        // Note: We use offsetLeft/Top which are relative to the offsetParent (machineFlow)
        // This gives us unscaled coordinates within the container.
        
        for (let i = 0; i < cards.length - 1; i++) {
            const current = cards[i];
            const next = cards[i + 1];

            // Calculate center points relative to container (unscaled)
            const x1 = current.offsetLeft + current.offsetWidth / 2;
            const y1 = current.offsetTop + current.offsetHeight / 2;
            const x2 = next.offsetLeft + next.offsetWidth / 2;
            const y2 = next.offsetTop + next.offsetHeight / 2;

            // Draw Arrow
            drawArrow(x1, y1, x2, y2, '#6366f1');

            // Add Swap Button at midpoint
            const midX = (x1 + x2) / 2;
            const midY = (y1 + y2) / 2;
            
            createSwapButton(midX, midY, current.dataset.machineId, next.dataset.machineId);
        }
    }


    function drawArrow(x1, y1, x2, y2, color) {
        const headlen = 12;
        const angle = Math.atan2(y2 - y1, x2 - x1);

        ctx.strokeStyle = color;
        ctx.fillStyle = color;
        ctx.lineWidth = 2;
        
        // Line
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();

        // Arrowhead (at 60% of path to avoid overlap with button or card)
        // Let's put it near the end but before the next card?
        // Actually, midpoint is where button is. Let's put arrow head near target.
        // We need to stop short of the target card.
        // Approx distance to stop: half card width/height.
        // Simple approach: standard arrowhead at end, obscured by card is fine if z-index is right?
        // No, canvas is behind. So line goes to center.
        // Let's just draw arrowhead slightly before the center of target card.
        
        const dist = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
        // Stop 175px short (half width) approx
        const t = Math.max(0.1, 1 - (180 / dist)); 
        
        const arrX = x1 + (x2 - x1) * t;
        const arrY = y1 + (y2 - y1) * t;

        ctx.beginPath();
        ctx.moveTo(arrX, arrY);
        ctx.lineTo(arrX - headlen * Math.cos(angle - Math.PI / 6), arrY - headlen * Math.sin(angle - Math.PI / 6));
        ctx.lineTo(arrX - headlen * Math.cos(angle + Math.PI / 6), arrY - headlen * Math.sin(angle + Math.PI / 6));
        ctx.fill();
    }

    function createSwapButton(x, y, id1, id2) {
        const btn = document.createElement('button');
        btn.className = 'swap-btn';
        btn.innerHTML = '<i class="fas fa-exchange-alt"></i>';
        btn.title = 'Swap Order';
        btn.style.position = 'absolute';
        btn.style.left = x + 'px';
        btn.style.top = y + 'px';
        btn.style.transform = 'translate(-50%, -50%)';
        btn.style.zIndex = '20';
        btn.style.padding = '8px';
        btn.style.borderRadius = '50%';
        btn.style.border = '1px solid #e2e8f0';
        btn.style.background = 'white';
        btn.style.color = '#64748b';
        btn.style.cursor = 'pointer';
        btn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        btn.style.transition = 'all 0.2s';

        btn.onmouseover = () => {
            btn.style.color = '#6366f1';
            btn.style.borderColor = '#6366f1';
            btn.style.transform = 'translate(-50%, -50%) scale(1.1)';
        };
        btn.onmouseout = () => {
            btn.style.color = '#64748b';
            btn.style.borderColor = '#e2e8f0';
            btn.style.transform = 'translate(-50%, -50%) scale(1)';
        };

        btn.onclick = () => swapMachines(id1, id2);

        document.getElementById('machineFlow').appendChild(btn);
    }

        async function swapMachines(id1, id2) {
        // Optimistic UI Update
        const card1 = document.querySelector(`.machine-card[data-machine-id="${id1}"]`);
        const card2 = document.querySelector(`.machine-card[data-machine-id="${id2}"]`);

        if (card1 && card2) {
            const parent = card1.parentNode;
            
            // Swap based on position
            if (card1.nextElementSibling === card2) {
                parent.insertBefore(card2, card1);
            } else if (card2.nextElementSibling === card1) {
                parent.insertBefore(card1, card2);
            } else {
                // Generic swap if not adjacent (unlikely here but safe)
                const folder = document.createElement('div');
                parent.insertBefore(folder, card1);
                parent.insertBefore(card1, card2);
                parent.insertBefore(card2, folder);
                folder.remove();
            }
            
            // Redraw immediately
            updateLayout(); 
        }

        try {
            const response = await fetch('/machines/swap-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    machine_id_1: id1,
                    machine_id_2: id2
                })
            });

            if (!response.ok) {
                console.error('Swap failed on server');
                // Revert or reload
                window.location.reload(); 
            }
        } catch (error) {
            console.error('Error swapping:', error);
            window.location.reload();
        }
    }


    // Keep Notification & Toggle Logic
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
        
        // Close notifications if open
        const notifDropdown = document.getElementById('notificationDropdown');
        if (notifDropdown) notifDropdown.classList.remove('show');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
        
        // Close user dropdown if open
        const userDropdown = document.getElementById('userDropdown');
        if (userDropdown) userDropdown.classList.remove('show');
        
        // Load notifications if opening
        if (dropdown.classList.contains('show')) {
            loadNotifications();
        }
    }

    async function loadNotifications() {
        const listEl = document.getElementById('notificationList');
        if(!listEl) return;
        listEl.innerHTML = `
            <div class="notification-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Loading...</span>
            </div>
        `;

        try {
            const response = await fetch('/notifications');
            const data = await response.json();
            
            updateNotificationBadge(data.unread_count);
            renderNotifications(data.notifications);
        } catch (error) {
            console.error('Error loading notifications:', error);
            listEl.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Failed to load notifications</p>
                </div>
            `;
        }
    }

    function getIconClass(icon) {
        const icons = {
            'zap': 'fa-bolt',
            'flame': 'fa-fire',
            'trophy': 'fa-trophy',
            'calendar': 'fa-calendar',
            'bell': 'fa-bell'
        };
        return icons[icon] || 'fa-bell';
    }

    function renderNotifications(notifications) {
        const listEl = document.getElementById('notificationList');
        
        if (notifications.length === 0) {
            listEl.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications yet</p>
                </div>
            `;
            return;
        }

        listEl.innerHTML = notifications.map(notif => `
            <div class="notification-item ${notif.is_read ? '' : 'unread'}" onclick="markNotificationAsRead(${notif.id})">
                <div class="notification-icon ${notif.color}">
                    <i class="fas ${getIconClass(notif.icon)}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notif.title}</div>
                    <div class="notification-message">${notif.message}</div>
                    <div class="notification-time">${formatTime(notif.created_at)}</div>
                </div>
            </div>
        `).join('');
    }

    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    async function markNotificationAsRead(id) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            });
            loadNotifications();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async function markAllAsRead() {
        try {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            });
            loadNotifications();
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // seconds

        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }

    // Card Menu Toggle
    function toggleCardMenu(event, machineId) {
        event.stopPropagation();
        event.preventDefault();
        
        // Close all other card menus first
        document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
            if (menu.id !== `cardMenu${machineId}`) {
                menu.classList.remove('show');
            }
        });
        
        const menu = document.getElementById(`cardMenu${machineId}`);
        menu.classList.toggle('show');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu');
        const userDropdown = document.getElementById('userDropdown');
        const notifBtn = document.querySelector('.notification-btn');
        const notifDropdown = document.getElementById('notificationDropdown');
        
        if (userMenu && userDropdown && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove('show');
        }
        
        if (notifBtn && notifDropdown && !notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.remove('show');
        }

        // Close card menus when clicking outside
        if (!event.target.closest('.card-menu-wrapper')) {
            document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Load notification count on page load
    window.addEventListener('load', function() {
        initializeFlow();
        fetch('/notifications')
            .then(res => res.json())
            .then(data => updateNotificationBadge(data.unread_count))
            .catch(err => console.error('Error loading notification count:', err));
    });

    // Zoom View Toggle
    let zoomLevel = 1.0; // 1.0 = 100%, 0.5 = 50%
    const minZoom = 0.5;
    const maxZoom = 1.5;
    const zoomStep = 0.1;
    let zoomTimeout;

    function updateZoomDisplay() {
        const flowWrapper = document.getElementById('machineFlowWrapper');
        const flow = document.getElementById('machineFlow');
        const canvas = document.getElementById('connectionCanvas');
        const icon = document.getElementById('zoomIcon');
        const text = document.getElementById('zoomText');
        const indicator = document.getElementById('zoomLevelIndicator');
        const percentage = document.getElementById('zoomPercentage');
        
        if (!flow) return;
        
        // Apply zoom transform to both flow and canvas
        flow.style.transform = `scale(${zoomLevel})`;
        flow.style.transformOrigin = 'top center';
        flow.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        
        // Scale canvas the same way
        if (canvas) {
            canvas.style.transform = `scale(${zoomLevel})`;
            canvas.style.transformOrigin = 'top center';
            canvas.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        }
        
        // Update button and show/hide subsystems
        if (zoomLevel < 0.8) {
            flow.classList.add('overview-mode');
            flowWrapper.classList.add('zoomed-out');
            icon.className = 'fas fa-search-plus';
            text.textContent = '{{ __("messages.detail") }}';
        } else {
            flow.classList.remove('overview-mode');
            flowWrapper.classList.remove('zoomed-out');
            icon.className = 'fas fa-search-minus';
            text.textContent = '{{ __("messages.overview") }}';
        }
        
        // Show zoom indicator
        percentage.textContent = Math.round(zoomLevel * 100);
        indicator.classList.add('show');
        
        // Hide indicator after 1 second
        clearTimeout(zoomTimeout);
        zoomTimeout = setTimeout(() => {
            indicator.classList.remove('show');
        }, 1000);
        
        // Redraw connections after zoom animation
        setTimeout(updateLayout, 350);
    }

    function toggleZoomView() {
        if (zoomLevel < 0.8) {
            zoomLevel = 1.0; // Zoom in to normal
        } else {
            zoomLevel = 0.6; // Zoom out to overview
        }
        updateZoomDisplay();
    }

    // Mouse wheel zoom
    document.addEventListener('wheel', function(e) {
        const flowWrapper = document.getElementById('machineFlowWrapper');
        if (!flowWrapper) return;
        
        // Check if mouse is over the machine flow area
        const rect = flowWrapper.getBoundingClientRect();
        if (e.clientX >= rect.left && e.clientX <= rect.right &&
            e.clientY >= rect.top && e.clientY <= rect.bottom) {
            
            // Prevent default scroll
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                
                // Zoom in/out
                if (e.deltaY < 0) {
                    // Scroll up - zoom in
                    zoomLevel = Math.min(maxZoom, zoomLevel + zoomStep);
                } else {
                    // Scroll down - zoom out
                    zoomLevel = Math.max(minZoom, zoomLevel - zoomStep);
                }
                
                updateZoomDisplay();
            }
        }
    }, { passive: false });

    // Initialize zoom on page load
    window.addEventListener('load', function() {
        updateZoomDisplay();
    });



</script>
@endpush
@endsection
