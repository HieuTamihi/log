@extends('layouts.app')

@section('title', 'System Sight - Business Machine')

@section('content')
<div class="ss-wrapper">
    <!-- Animated Background -->
    <div class="bg-gradient"></div>
    
    <!-- Navbar Component -->
    <x-navbar />
    
    <!-- Floating Action Button -->
    <a href="{{ route('machines.create') }}" class="fab-btn" title="{{ __('messages.create_machine') }}">
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

            <div style="margin-bottom: 24px;">
                @if($streak && $streak->current_streak > 0)
                <div class="streak-badge">
                    <span class="streak-icon">🔥</span>
                    <span class="streak-text">{{ __('messages.streak') }}: <strong>{{ $streak->current_streak }}</strong> {{ $streak->current_streak === 1 ? __('messages.week') : __('messages.weeks') }} {{ __('messages.shipping_upgrades') }}</span>
                </div>
                @endif
            </div>

            <!-- Ship Upgrade Button -->
            <div class="main-action">
                <div class="quick-ship-wrapper">
                    <button class="btn-ship" onclick="toggleQuickShip()">
                        <i class="fas fa-bolt"></i>
                        <span>{{ __('messages.ship_an_upgrade') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <!-- Quick Ship Dropdown -->
                    <div class="quick-ship-dropdown" id="quickShipDropdown">
                        <div class="quick-ship-header">
                            <h4>{{ __('messages.quick_ship') }}</h4>
                            <p>{{ __('messages.components_need_attention') }}</p>
                        </div>
                        <div class="quick-ship-list">
                            @php
                                $needsAttention = \App\Models\Component::whereIn('health_status', ['on_fire', 'needs_love'])
                                    ->with(['subsystem.machine'])
                                    ->orderByRaw("FIELD(health_status, 'on_fire', 'needs_love')")
                                    ->limit(5)
                                    ->get();
                            @endphp
                            
                            @forelse($needsAttention as $component)
                            <a href="{{ route('upgrades.create', $component->id) }}" class="quick-ship-item">
                                <div class="quick-ship-icon {{ $component->health_status === 'on_fire' ? 'danger' : 'warning' }}">
                                    <i class="fas {{ $component->health_status === 'on_fire' ? 'fa-fire' : 'fa-heart' }}"></i>
                                </div>
                                <div class="quick-ship-info">
                                    <div class="quick-ship-name">{{ $component->name }}</div>
                                    <div class="quick-ship-path">{{ $component->subsystem->machine->name }} → {{ $component->subsystem->name }}</div>
                                </div>
                                <i class="fas fa-chevron-right quick-ship-arrow"></i>
                            </a>
                            @empty
                            <div class="quick-ship-empty">
                                <i class="fas fa-check-circle"></i>
                                <p>{{ __('messages.all_components_smooth') }}</p>
                                <small>{{ __('messages.browse_machines') }}</small>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Machine Flow Visualization -->
            <div class="machine-flow-wrapper">
                <div class="machine-flow">
                    @foreach($machines as $index => $machine)
                    <div class="machine-card" style="animation-delay: {{ $index * 0.15 }}s">
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
                                    <span>Chỉnh sửa</span>
                                </a>
                                <form action="{{ route('machines.destroy', $machine) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete_machine') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="card-menu-item danger">
                                        <i class="fas fa-trash"></i>
                                        <span>Xóa</span>
                                    </button>
                                </form>
                            </div>
                        </div>


                        <h3 class="machine-name">{{ $machine->name }}</h3>
                        <p class="machine-description">{{ $machine->description }}</p>

                        <!-- Machine Metrics -->
                        <div class="machine-metrics">
                            @php
                                $componentsCount = $machine->components->count();
                                $onFireCount = $machine->components->where('health_status', 'on_fire')->count();
                                $needsLoveCount = $machine->components->where('health_status', 'needs_love')->count();
                                // Get first component with metric for display
                                $componentWithMetric = $machine->components->first(function($c) {
                                    return $c->metric_value && $c->metric_label;
                                });
                                // Get first component with current_issue
                                $componentWithIssue = $machine->components->first(function($c) {
                                    return $c->current_issue;
                                });
                            @endphp
                            
                            @if($onFireCount > 0)
                                <div class="metric-badge danger">
                                    <span class="metric-icon">🔥</span>
                                    <span>{{ $onFireCount }} {{ __('messages.on_fire') }}</span>
                                </div>
                            @elseif($needsLoveCount > 0)
                                <div class="metric-badge warning">
                                    <span class="metric-icon">💛</span>
                                    <span>{{ __('messages.needs_love') }}</span>
                                </div>
                            @else
                                <div class="metric-badge success">
                                    <span class="metric-icon">✅</span>
                                    <span>{{ __('messages.smooth') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('machines.show', $machine->slug) }}" class="btn-upgrade">
                            <span>{{ __('messages.upgrade') }}</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>

                        @if(!$loop->last)
                        <div class="flow-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Next Upgrades Section -->
            <div class="next-upgrades">
                <h3 class="section-title">{{ __('messages.next_upgrades') }}</h3>
                <div class="upgrade-chips">
                    @php
                        $pendingUpgrades = \App\Models\Upgrade::where('status', '!=', 'shipped')
                            ->with('component')
                            ->orderBy('created_at', 'desc')
                            ->limit(4)
                            ->get();
                    @endphp
                    
                    @forelse($pendingUpgrades as $upgrade)
                    <a href="{{ route('upgrades.edit', $upgrade) }}" class="chip">
                        <span class="chip-icon">{{ $upgrade->status === 'active' ? '⚡' : '✨' }}</span>
                        <span>{{ Str::limit($upgrade->title, 25) }}</span>
                    </a>
                    @empty
                    <span class="chip chip-empty">
                        <span class="chip-icon">📝</span>
                        <span>{{ __('messages.no_pending_upgrades') }}</span>
                    </span>
                    @endforelse
                    
                    <button class="chip chip-add" onclick="{{ $needsAttention->first() ? "window.location.href='" . route('upgrades.create', $needsAttention->first()->id) . "'" : "alert('" . __('messages.all_components_smooth') . "')" }}">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

@push('styles')
<style>
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

    /* ===== MACHINE FLOW ===== */
    .machine-flow-wrapper {
        margin-bottom: 56px;
    }

    .machine-flow {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 50px;
    }

    @media (min-width: 1200px) {
        .machine-flow {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .machine-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.3s;
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

    /* Flow Arrow Connector between cards */
    .flow-arrow {
        position: absolute;
        right: -41px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .flow-arrow i {
        width: 32px;
        height: 32px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #6366f1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .machine-card {
        position: relative;
    }

    /* Connector line before arrow */
    .flow-arrow::before {
        content: '';
        position: absolute;
        right: 100%;
        top: 50%;
        width: 12px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #e2e8f0);
    }

    /* Connector line after arrow */
    .flow-arrow::after {
        content: '';
        position: absolute;
        left: 100%;
        top: 50%;
        width: 12px;
        height: 2px;
        background: linear-gradient(90deg, #e2e8f0, transparent);
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
    function toggleQuickShip() {
        const dropdown = document.getElementById('quickShipDropdown');
        dropdown.classList.toggle('show');
        
        // Close other dropdowns
        const notifDropdown = document.getElementById('notificationDropdown');
        const userDropdown = document.getElementById('userDropdown');
        if (notifDropdown) notifDropdown.classList.remove('show');
        if (userDropdown) userDropdown.classList.remove('show');
    }

    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
        
        // Close notifications if open
        const notifDropdown = document.getElementById('notificationDropdown');
        const quickShipDropdown = document.getElementById('quickShipDropdown');
        if (notifDropdown) notifDropdown.classList.remove('show');
        if (quickShipDropdown) quickShipDropdown.classList.remove('show');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
        
        // Close user dropdown if open
        const userDropdown = document.getElementById('userDropdown');
        const quickShipDropdown = document.getElementById('quickShipDropdown');
        if (userDropdown) userDropdown.classList.remove('show');
        if (quickShipDropdown) quickShipDropdown.classList.remove('show');
        
        // Load notifications if opening
        if (dropdown.classList.contains('show')) {
            loadNotifications();
        }
    }

    async function loadNotifications() {
        const listEl = document.getElementById('notificationList');
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
        const quickShipBtn = document.querySelector('.btn-ship');
        const quickShipDropdown = document.getElementById('quickShipDropdown');
        
        if (userMenu && userDropdown && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove('show');
        }
        
        if (notifBtn && notifDropdown && !notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.remove('show');
        }
        
        if (quickShipBtn && quickShipDropdown && !quickShipBtn.contains(event.target) && !quickShipDropdown.contains(event.target)) {
            quickShipDropdown.classList.remove('show');
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
        fetch('/notifications')
            .then(res => res.json())
            .then(data => updateNotificationBadge(data.unread_count))
            .catch(err => console.error('Error loading notification count:', err));
    });
</script>
@endpush
@endsection
