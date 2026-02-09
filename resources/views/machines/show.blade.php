@extends('layouts.app')

@section('title', $machine->name . ' Machine - System Sight')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    
    <!-- Header -->
    <x-navbar />

    <main class="ss-main">
        <div class="ss-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('dashboard') }}" class="breadcrumb-item">Business Machine</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">{{ $machine->name }}</span>
            </div>

            <!-- Page Header -->
            <div class="page-header-detail">
                <div class="machine-icon-large" style="display: none;">{{ $machine->icon }}</div>
                <div style="flex: 1;">
                    <h1 class="page-title-detail">{{ $machine->name }} Machine</h1>
                    <p class="page-subtitle">{{ $machine->description }}</p>
                    @if($machine->user)
                    <p class="creator-info">👤 Người tạo: <strong>{{ $machine->user->username }}</strong> • {{ $machine->created_at->diffForHumans() }}</p>
                    @endif
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('machines.edit', $machine) }}" class="btn-secondary">
                        <i data-lucide="edit-2"></i>
                        <span>{{ __('messages.edit_machine') }}</span>
                    </a>
                    <a href="{{ route('subsystems.create', $machine) }}" class="btn-primary">
                        <i data-lucide="plus"></i>
                        <span>{{ __('messages.create_subsystem') }}</span>
                    </a>
                </div>
            </div>

            @if($streak && $streak->current_streak > 0)
            <div class="streak-badge">
                <span class="streak-icon">🔥</span>
                <span class="streak-text">Streak: <strong>{{ $streak->current_streak }}</strong> {{ $streak->current_streak === 1 ? 'week' : 'weeks' }}</span>
            </div>
            @endif

            <!-- Subsystems Grid -->
            <div class="subsystems-grid">
                @foreach($machine->subsystems as $subsystem)
                <div class="subsystem-card">
                    <h3 class="subsystem-name">{{ $subsystem->name }}</h3>
                    <p class="subsystem-description">{{ $subsystem->description }}</p>

                    <div class="subsystem-metrics">
                        @php
                            $componentsCount = $subsystem->components->count();
                            $onFireCount = $subsystem->components->where('health_status', 'on_fire')->count();
                            $needsLoveCount = $subsystem->components->where('health_status', 'needs_love')->count();
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

                    <a href="{{ route('subsystems.show', ['machineSlug' => $machine->slug, 'subsystemSlug' => $subsystem->slug]) }}" class="btn-upgrade">
                        <span>View Details</span>
                        <i data-lucide="chevron-right"></i>
                    </a>
                    
                    <a href="{{ route('subsystems.edit', $subsystem) }}" class="btn-secondary" style="margin-top: 8px;">
                        <i data-lucide="edit-2"></i>
                        <span>{{ __('messages.edit_subsystem') }}</span>
                    </a>
                    
                    @if(!$loop->last)
                    <div class="flow-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Recent Upgrades Section -->
            @php
                $allUpgrades = collect();
                foreach($machine->subsystems as $subsystem) {
                    foreach($subsystem->components as $component) {
                        foreach($component->upgrades as $upgrade) {
                            $upgrade->component_name = $component->name;
                            $upgrade->subsystem_name = $subsystem->name;
                            $allUpgrades->push($upgrade);
                        }
                    }
                }
                $allUpgrades = $allUpgrades->sortByDesc('created_at');
            @endphp

            @if($allUpgrades->count() > 0)
            <div class="upgrades-section">
                <h2 class="section-title">🚀 Các Cải tiến ({{ $allUpgrades->count() }})</h2>
                
                <div class="upgrades-list-full">
                    @foreach($allUpgrades as $upgrade)
                    <div class="upgrade-card-full">
                        <div class="upgrade-card-header-full">
                            <div class="upgrade-card-left">
                                <span class="upgrade-status-dot status-{{ $upgrade->status }}"></span>
                                <div>
                                    <h4 class="upgrade-title">{{ $upgrade->name }}</h4>
                                    <span class="upgrade-location">📍 {{ $upgrade->subsystem_name }} → {{ $upgrade->component_name }}</span>
                                </div>
                            </div>
                            <div class="upgrade-card-right">
                                <span class="upgrade-status-badge status-{{ $upgrade->status }}">
                                    {{ $upgrade->status === 'shipped' ? '✅ Hoàn thành' : ($upgrade->status === 'active' ? '⚡ Đang làm' : '📝 Nháp') }}
                                </span>
                                <span class="upgrade-date">{{ $upgrade->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        
                        <div class="upgrade-card-content">
                            @if($upgrade->purpose)
                            <div class="upgrade-field">
                                <strong>🎯 Mục đích:</strong>
                                <p>{{ $upgrade->purpose }}</p>
                            </div>
                            @endif
                            
                            @if($upgrade->trigger)
                            <div class="upgrade-field">
                                <strong>⚡ Kích hoạt:</strong>
                                <p>{{ $upgrade->trigger }}</p>
                            </div>
                            @endif
                            
                            @if($upgrade->steps && count($upgrade->steps) > 0)
                            <div class="upgrade-field">
                                <strong>📋 Các bước:</strong>
                                <ol class="upgrade-steps-list">
                                    @foreach($upgrade->steps as $step)
                                    <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>
                            @endif
                            
                            @if($upgrade->definition_of_done)
                            <div class="upgrade-field">
                                <strong>✅ Tiêu chí hoàn thành:</strong>
                                <p>{{ $upgrade->definition_of_done }}</p>
                            </div>
                            @endif
                            
                            @if($upgrade->shipped_at)
                            <div class="upgrade-shipped-info">
                                🚀 Hoàn thành: {{ $upgrade->shipped_at->format('d/m/Y H:i') }}
                            </div>
                            @endif
                        </div>
                        
                        <div class="upgrade-card-actions">
                            <a href="{{ route('upgrades.edit', $upgrade) }}" class="btn-sm-secondary">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
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

    .search-box svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input {
        padding: 9px 12px 9px 38px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        width: 240px;
        font-size: 14px;
        background: white;
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
    }

    .user-menu {
        position: relative;
        padding: 3px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
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

    .user-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 160px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: none;
        padding: 4px;
        z-index: 1000;
    }

    .user-dropdown.show {
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
        font-size: 14px;
        color: #ef4444;
        cursor: pointer;
        border-radius: 8px;
        font-weight: 500;
    }

    .dropdown-item:hover {
        background: #fef2f2;
    }

    .ss-main {
        padding: 40px 0 80px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        font-size: 14px;
    }

    .breadcrumb-item {
        color: #64748b;
        text-decoration: none;
    }

    .breadcrumb-item:hover {
        color: #6366f1;
    }

    .breadcrumb-item.active {
        color: #1a202c;
        font-weight: 500;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
    }

    .page-header-detail {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 32px;
    }

    .machine-icon-large {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        border: 1px solid #e2e8f0;
    }

    .page-title-detail {
        font-size: 36px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 16px;
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
        margin-bottom: 32px;
    }

    .subsystems-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 50px;
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

    .subsystem-card {
        position: relative;
        background: white;
        border-radius: 16px;
        padding: 28px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
    }

    @media (max-width: 768px) {
        .page-header-detail {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .page-header-detail > div:nth-child(2) {
            width: 100%;
        }

        .page-header-detail > div:last-child {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }

        .page-title-detail {
            font-size: 28px;
            line-height: 1.2;
        }

        .btn-primary, .btn-secondary {
            width: 100%;
            justify-content: center;
        }
        
        .subsystems-grid {
            gap: 24px;
        }

        .flow-arrow {
            display: none; /* Hide flow arrows on mobile as they break vertical layout */
        }
    }

    .subsystem-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .subsystem-header {
        margin-bottom: 20px;
    }

    .subsystem-icon-wrapper {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
    }

    .subsystem-icon {
        font-size: 32px;
    }

    .subsystem-name {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #1a202c;
    }

    .subsystem-description {
        color: #64748b;
        font-size: 15px;
        margin-bottom: 20px;
    }

    .subsystem-metrics {
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
    }

    .subsystem-metric-display {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 16px;
    }

    .subsystem-metric-display .metric-value {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
    }

    .subsystem-metric-display .metric-label {
        font-size: 14px;
        font-weight: 500;
        color: #64748b;
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

    .btn-upgrade:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
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

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* Upgrades Section */
    .upgrades-section {
        margin-top: 48px;
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 24px;
    }

    .upgrades-list-full {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .upgrade-card-full {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .upgrade-card-full:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .upgrade-card-header-full {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        gap: 16px;
        flex-wrap: wrap;
    }

    .upgrade-card-left {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .upgrade-status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }

    .upgrade-status-dot.status-shipped {
        background: #10b981;
    }

    .upgrade-status-dot.status-active {
        background: #f59e0b;
    }

    .upgrade-status-dot.status-draft {
        background: #94a3b8;
    }

    .upgrade-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a202c;
        margin: 0 0 4px 0;
    }

    .upgrade-location {
        font-size: 13px;
        color: #64748b;
    }

    .upgrade-card-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .upgrade-status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .upgrade-status-badge.status-shipped {
        background: #d1fae5;
        color: #065f46;
    }

    .upgrade-status-badge.status-active {
        background: #fef3c7;
        color: #92400e;
    }

    .upgrade-status-badge.status-draft {
        background: #f1f5f9;
        color: #475569;
    }

    .upgrade-date {
        font-size: 13px;
        color: #94a3b8;
    }

    .upgrade-card-content {
        padding: 24px;
    }

    .upgrade-field {
        margin-bottom: 20px;
    }

    .upgrade-field:last-child {
        margin-bottom: 0;
    }

    .upgrade-field strong {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .upgrade-field p {
        font-size: 15px;
        color: #1a202c;
        line-height: 1.7;
        margin: 0;
    }

    .upgrade-steps-list {
        margin: 0;
        padding-left: 20px;
        font-size: 15px;
        color: #1a202c;
        line-height: 1.8;
    }

    .upgrade-steps-list li {
        margin-bottom: 6px;
    }

    .upgrade-shipped-info {
        background: #d1fae5;
        padding: 12px 16px;
        border-radius: 10px;
        color: #065f46;
        font-weight: 500;
        font-size: 14px;
        margin-top: 16px;
    }

    .upgrade-card-actions {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        background: #fafbfc;
    }

    .btn-sm-secondary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-sm-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    @media (max-width: 768px) {
        .upgrade-card-header-full {
            flex-direction: column;
            padding: 16px;
        }

        .upgrade-card-right {
            width: 100%;
            justify-content: flex-start;
        }

        .upgrade-card-content {
            padding: 16px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu');
        const dropdown = document.getElementById('userDropdown');
        
        if (!userMenu.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
@endpush
@endsection
