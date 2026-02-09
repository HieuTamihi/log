@extends('layouts.app')

@section('title', $subsystem->name . ' - System Sight')

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
                <a href="{{ route('machines.show', $subsystem->machine->slug) }}" class="breadcrumb-item">{{ $subsystem->machine->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">{{ $subsystem->name }}</span>
            </div>

            <!-- Page Header -->
            <div class="page-header-detail">
                <div class="subsystem-icon-large">{{ $subsystem->icon }}</div>
                <div style="flex: 1;">
                    <h1 class="page-title-detail">{{ $subsystem->name }}</h1>
                    <p class="page-subtitle">{{ $subsystem->description }}</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('subsystems.edit', $subsystem) }}" class="btn-secondary">
                        <i data-lucide="edit-2"></i>
                        <span>{{ __('messages.edit_subsystem') }}</span>
                    </a>
                    <a href="{{ route('components.create', $subsystem) }}" class="btn-primary">
                        <i data-lucide="plus"></i>
                        <span>{{ __('messages.create_component') }}</span>
                    </a>
                </div>
            </div>

            @if($streak && $streak->current_streak > 0)
            <div class="streak-badge">
                <span class="streak-icon">🔥</span>
                <span class="streak-text">Streak: <strong>{{ $streak->current_streak }}</strong> {{ $streak->current_streak === 1 ? 'week' : 'weeks' }}</span>
            </div>
            @endif

            <!-- Components Grid -->
            <div class="components-grid">
                @forelse($subsystem->components as $component)
                <div class="component-card">
                    <div class="component-header">
                        <div class="component-status-badge status-{{ $component->health_status }}">
                        @if($component->health_status === 'smooth')
                            <span class="status-icon">✅</span>
                            <span>{{ __('messages.smooth') }}</span>
                        @elseif($component->health_status === 'on_fire')
                            <span class="status-icon">🔥</span>
                            <span>{{ __('messages.on_fire') }}</span>
                        @else
                            <span class="status-icon">💛</span>
                            <span>{{ __('messages.needs_love') }}</span>
                        @endif
                        </div>
                    </div>
                    
                    <h3 class="component-name">{{ $component->name }}</h3>
                    <p class="component-description">{{ $component->description }}</p>

                    <!-- Metric Display -->
                    @if($component->metric_value && $component->metric_label)
                    <div class="component-metric-display">
                        <span class="metric-value">{{ $component->metric_value }}</span>
                        <span class="metric-label">{{ $component->metric_label }}</span>
                    </div>
                    @endif

                    <div class="component-upgrades">
                        @php
                            $upgradesCount = $component->upgrades->count();
                            $activeUpgrades = $component->upgrades->where('status', 'active')->count();
                        @endphp
                        
                        @if($upgradesCount > 0)
                            <div class="upgrade-count">
                                <i class="fas fa-bolt"></i>
                                <span>{{ $upgradesCount }} {{ $upgradesCount === 1 ? 'upgrade' : 'upgrades' }}</span>
                            </div>
                        @endif

                        @if($activeUpgrades > 0)
                            <div class="active-badge">
                                <span>{{ $activeUpgrades }} active</span>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('upgrades.create', $component->id) }}" class="btn-ship-upgrade">
                        <i data-lucide="zap"></i>
                        <span>Ship Upgrade</span>
                    </a>

                    <div style="display: flex; gap: 8px; margin-top: 12px; margin-bottom: 16px;">
                        <a href="{{ route('components.edit', $component) }}" class="btn-secondary" style="flex: 1; justify-content: center; align-items: center; display: flex; gap: 8px;">
                            <i data-lucide="edit-2" style="width: 16px; height: 16px;"></i>
                            <span>{{ __('messages.edit_component') }}</span>
                        </a>
                        
                        <form action="{{ route('components.destroy', $component) }}" method="POST" 
                              onsubmit="return confirm('{{ __('messages.confirm_delete_component') }}')"
                              style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary" style="background: #fee2e2; color: #ef4444; border-color: #fecaca; height: 100%; padding: 0 16px;">
                                <i data-lucide="trash-2">Xóa</i>
                            </button>
                        </form>
                    </div>

                    @if($component->upgrades->count() > 0)
                    <div class="upgrades-list">
                        <div class="upgrades-header">
                            <span>Cải tiến ({{ $component->upgrades->count() }})</span>
                        </div>
                        @foreach($component->upgrades->sortByDesc('created_at') as $upgrade)
                        <div class="upgrade-card">
                            <div class="upgrade-card-header">
                                <div class="upgrade-card-title">
                                    <span class="upgrade-status-dot status-{{ $upgrade->status }}"></span>
                                    <span class="upgrade-name">{{ $upgrade->name }}</span>
                                </div>
                                <div class="upgrade-card-meta">
                                    <span class="upgrade-status-badge status-{{ $upgrade->status }}">
                                        {{ $upgrade->status === 'shipped' ? '✅ Hoàn thành' : ($upgrade->status === 'active' ? '⚡ Đang làm' : '📝 Nháp') }}
                                    </span>
                                    <span class="upgrade-date">{{ $upgrade->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="upgrade-card-body">
                                @if($upgrade->purpose)
                                <div class="upgrade-section">
                                    <strong>🎯 Mục đích:</strong>
                                    <p>{{ $upgrade->purpose }}</p>
                                </div>
                                @endif
                                
                                @if($upgrade->trigger)
                                <div class="upgrade-section">
                                    <strong>⚡ Kích hoạt:</strong>
                                    <p>{{ $upgrade->trigger }}</p>
                                </div>
                                @endif
                                
                                @if($upgrade->steps && count($upgrade->steps) > 0)
                                <div class="upgrade-section">
                                    <strong>📋 Các bước:</strong>
                                    <ol class="upgrade-steps">
                                        @foreach($upgrade->steps as $step)
                                        <li>{{ $step }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                                @endif
                                
                                @if($upgrade->definition_of_done)
                                <div class="upgrade-section">
                                    <strong>✅ Tiêu chí hoàn thành:</strong>
                                    <p>{{ $upgrade->definition_of_done }}</p>
                                </div>
                                @endif
                                
                                @if($upgrade->shipped_at)
                                <div class="upgrade-section shipped-info">
                                    <strong>🚀 Hoàn thành:</strong> {{ $upgrade->shipped_at->format('d/m/Y H:i') }}
                                </div>
                                @endif
                                
                                <div class="upgrade-actions">
                                    <a href="{{ route('upgrades.edit', $upgrade) }}" class="btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Chỉnh sửa
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h3>No components yet</h3>
                    <p>Components will appear here once they're added to this subsystem.</p>
                </div>
                @endforelse
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

    .subsystem-icon-large {
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

    .components-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 24px;
    }

    .component-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
    }

    .component-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    }

    .component-header {
        margin-bottom: 16px;
    }

    .component-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .component-status-badge.status-smooth {
        background: #d1fae5;
        color: #065f46;
    }

    .component-status-badge.status-on_fire {
        background: #fee2e2;
        color: #991b1b;
    }

    .component-status-badge.status-needs_love {
        background: #fef3c7;
        color: #92400e;
    }

    .component-name {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #1a202c;
    }

    .component-description {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .component-metric-display {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 16px;
    }

    .component-metric-display .metric-value {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
    }

    .component-metric-display .metric-label {
        font-size: 14px;
        font-weight: 500;
        color: #64748b;
    }

    .component-upgrades {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .upgrade-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }

    .upgrade-count i {
        width: 16px;
        height: 16px;
    }

    .active-badge {
        padding: 6px 12px;
        background: #d1fae5;
        border-radius: 8px;
        font-size: 13px;
        color: #065f46;
        font-weight: 600;
    }

    .btn-ship-upgrade {
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
        margin-bottom: 16px;
    }

    .btn-ship-upgrade i {
        width: 18px;
        height: 18px;
    }

    .btn-ship-upgrade:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .upgrades-list {
        border-top: 1px solid #e2e8f0;
        padding-top: 16px;
    }

    .upgrades-header {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
    }

    .upgrade-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .upgrade-item:hover {
        background: #f1f5f9;
        transform: translateX(4px);
    }

    .upgrade-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .upgrade-name {
        font-size: 14px;
        font-weight: 600;
        color: #1a202c;
    }

    .upgrade-date {
        font-size: 12px;
        color: #94a3b8;
    }

    .upgrade-status-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .upgrade-status-badge.status-draft {
        background: #f1f5f9;
        color: #64748b;
    }

    .upgrade-status-badge.status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748b;
        font-size: 15px;
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

    /* Upgrade Card Styles */
    .upgrade-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .upgrade-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .upgrade-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        gap: 12px;
    }

    .upgrade-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }

    .upgrade-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
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

    .upgrade-card .upgrade-name {
        font-weight: 600;
        color: #1a202c;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .upgrade-card-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .upgrade-chevron {
        color: #94a3b8;
        transition: transform 0.2s ease;
        font-size: 12px;
    }

    .upgrade-chevron.rotated {
        transform: rotate(180deg);
    }

    .upgrade-card-body {
        padding: 0 16px 16px;
        border-top: 1px solid #e2e8f0;
        background: white;
    }

    .upgrade-section {
        margin-top: 14px;
    }

    .upgrade-section strong {
        display: block;
        color: #475569;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .upgrade-section p {
        color: #1a202c;
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    .upgrade-steps {
        margin: 0;
        padding-left: 20px;
        color: #1a202c;
        font-size: 14px;
        line-height: 1.8;
    }

    .upgrade-steps li {
        margin-bottom: 4px;
    }

    .shipped-info {
        background: #d1fae5;
        padding: 10px 14px;
        border-radius: 8px;
        color: #065f46;
    }

    .upgrade-actions {
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: 13px;
        border-radius: 8px;
    }

    .btn-sm.btn-secondary {
        padding: 8px 14px;
    }

    .upgrades-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        color: #475569;
        margin-bottom: 12px;
        font-size: 14px;
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
        
        .components-grid {
            grid-template-columns: 1fr;
        }
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
        
        .components-grid {
            grid-template-columns: 1fr;
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

    // Toggle upgrade card expand/collapse
    function toggleUpgrade(id) {
        event.stopPropagation();
        const body = document.getElementById('upgrade-body-' + id);
        const chevron = document.getElementById('chevron-' + id);
        
        if (body.style.display === 'none') {
            body.style.display = 'block';
            chevron.classList.add('rotated');
        } else {
            body.style.display = 'none';
            chevron.classList.remove('rotated');
        }
    }
</script>
@endpush
@endsection
