@extends('layouts.app')

@section('title', 'Search Results - System Sight')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    
    <!-- Header -->
    <header class="ss-header">
        <div class="ss-container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <rect width="32" height="32" rx="8" fill="url(#logo-gradient)"/>
                            <text x="16" y="22" text-anchor="middle" fill="white" font-size="16" font-weight="700">Ss</text>
                            <defs>
                                <linearGradient id="logo-gradient" x1="0" y1="0" x2="32" y2="32">
                                    <stop offset="0%" stop-color="#667eea"/>
                                    <stop offset="100%" stop-color="#764ba2"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <span class="logo-text">System Sight</span>
                </div>
                
                <div class="header-actions">
                    <form action="{{ route('search') }}" method="GET" class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="search" name="q" placeholder="Search..." class="search-input" value="{{ $query }}" autofocus>
                    </form>
                    <div class="notification-wrapper">
                        <button class="icon-btn notification-btn" onclick="toggleNotifications()">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>
                        
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h3>Notifications</h3>
                                <button class="btn-mark-all-read" onclick="markAllAsRead()">
                                    <i class="fas fa-check-double"></i>
                                    <span>Mark all read</span>
                                </button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="notification-loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="user-menu" onclick="toggleUserDropdown()">
                        <div class="user-avatar">{{ substr(Auth::user()->username, 0, 1) }}</div>
                    </div>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="ss-main">
        <div class="ss-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('dashboard') }}" class="breadcrumb-item">Business Machine</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">Search Results</span>
            </div>

            <!-- Search Header -->
            <div class="search-header">
                <h1 class="search-title">
                    @if($results['total'] > 0)
                        Found {{ $results['total'] }} {{ $results['total'] === 1 ? 'result' : 'results' }}
                    @else
                        No results found
                    @endif
                </h1>
                @if($query)
                <p class="search-subtitle">for "{{ $query }}"</p>
                @endif
            </div>

            @if($results['total'] > 0)
                <!-- Machines Results -->
                @if($results['machines']->count() > 0)
                <div class="results-section">
                    <h2 class="section-title">
                        <i class="fas fa-cog"></i>
                        <span>Machines ({{ $results['machines']->count() }})</span>
                    </h2>
                    <div class="results-grid">
                        @foreach($results['machines'] as $machine)
                        <a href="{{ route('machines.show', $machine->slug) }}" class="result-card">
                            <div class="result-icon">{{ $machine->icon }}</div>
                            <div class="result-content">
                                <h3 class="result-title">{{ $machine->name }}</h3>
                                <p class="result-description">{{ Str::limit($machine->description, 100) }}</p>
                                <div class="result-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-layer-group"></i>
                                        {{ $machine->subsystems->count() }} subsystems
                                    </span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right result-arrow"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Subsystems Results -->
                @if($results['subsystems']->count() > 0)
                <div class="results-section">
                    <h2 class="section-title">
                        <i class="fas fa-layer-group"></i>
                        <span>Subsystems ({{ $results['subsystems']->count() }})</span>
                    </h2>
                    <div class="results-grid">
                        @foreach($results['subsystems'] as $subsystem)
                        <a href="{{ route('subsystems.show', ['machineSlug' => $subsystem->machine->slug, 'subsystemSlug' => $subsystem->slug]) }}" class="result-card">
                            <div class="result-icon">{{ $subsystem->icon }}</div>
                            <div class="result-content">
                                <h3 class="result-title">{{ $subsystem->name }}</h3>
                                <p class="result-description">{{ Str::limit($subsystem->description, 100) }}</p>
                                <div class="result-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-cog"></i>
                                        {{ $subsystem->machine->name }}
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-cube"></i>
                                        {{ $subsystem->components->count() }} components
                                    </span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right result-arrow"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Components Results -->
                @if($results['components']->count() > 0)
                <div class="results-section">
                    <h2 class="section-title">
                        <i class="fas fa-cube"></i>
                        <span>Components ({{ $results['components']->count() }})</span>
                    </h2>
                    <div class="results-grid">
                        @foreach($results['components'] as $component)
                        <a href="{{ route('subsystems.show', ['machineSlug' => $component->subsystem->machine->slug, 'subsystemSlug' => $component->subsystem->slug]) }}" class="result-card">
                            <div class="result-status status-{{ $component->health_status }}">
                                @if($component->health_status === 'on_fire')
                                    🔥
                                @elseif($component->health_status === 'needs_love')
                                    💛
                                @else
                                    ✅
                                @endif
                            </div>
                            <div class="result-content">
                                <h3 class="result-title">{{ $component->name }}</h3>
                                <p class="result-description">{{ Str::limit($component->description, 100) }}</p>
                                <div class="result-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-cog"></i>
                                        {{ $component->subsystem->machine->name }}
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-layer-group"></i>
                                        {{ $component->subsystem->name }}
                                    </span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right result-arrow"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Upgrades Results -->
                @if($results['upgrades']->count() > 0)
                <div class="results-section">
                    <h2 class="section-title">
                        <i class="fas fa-bolt"></i>
                        <span>Upgrades ({{ $results['upgrades']->count() }})</span>
                    </h2>
                    <div class="results-grid">
                        @foreach($results['upgrades'] as $upgrade)
                        <a href="{{ route('upgrades.edit', $upgrade) }}" class="result-card">
                            <div class="result-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div class="result-content">
                                <h3 class="result-title">{{ $upgrade->name }}</h3>
                                <p class="result-description">{{ Str::limit($upgrade->purpose, 100) }}</p>
                                <div class="result-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-cube"></i>
                                        {{ $upgrade->component->name }}
                                    </span>
                                    @if($upgrade->status === 'active')
                                    <span class="meta-badge active">
                                        <i class="fas fa-check-circle"></i>
                                        Shipped
                                    </span>
                                    @else
                                    <span class="meta-badge draft">
                                        <i class="fas fa-edit"></i>
                                        Draft
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-chevron-right result-arrow"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
                <!-- No Results -->
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2 class="no-results-title">No results found</h2>
                    <p class="no-results-text">Try searching with different keywords or check your spelling</p>
                    <a href="{{ route('dashboard') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
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
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 32px;
    }

    /* Header styles (same as dashboard) */
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
        text-decoration: none;
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
        pointer-events: none;
    }

    .search-input {
        padding: 9px 12px 9px 38px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        width: 300px;
        font-size: 14px;
        background: white;
        color: #1a202c;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        width: 350px;
    }

    .search-input::placeholder {
        color: #cbd5e1;
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

    /* Main Content */
    .ss-main {
        padding: 40px 0 80px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 32px;
        font-size: 14px;
        flex-wrap: wrap;
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

    /* Search Header */
    .search-header {
        text-align: center;
        margin-bottom: 48px;
    }

    .search-title {
        font-size: 36px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .search-subtitle {
        font-size: 18px;
        color: #64748b;
    }

    /* Results Section */
    .results-section {
        margin-bottom: 48px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 20px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 20px;
    }

    .section-title i {
        color: #6366f1;
        font-size: 18px;
    }

    .results-grid {
        display: grid;
        gap: 16px;
    }

    .result-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .result-card:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }

    .result-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
    }

    .result-icon i {
        color: #6366f1;
    }

    .result-status {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .result-status.status-smooth {
        background: #d1fae5;
    }

    .result-status.status-on_fire {
        background: #fee2e2;
    }

    .result-status.status-needs_love {
        background: #fef3c7;
    }

    .result-content {
        flex: 1;
        min-width: 0;
    }

    .result-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .result-description {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .result-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #94a3b8;
    }

    .meta-item i {
        font-size: 12px;
    }

    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .meta-badge.active {
        background: #d1fae5;
        color: #065f46;
    }

    .meta-badge.draft {
        background: #f1f5f9;
        color: #475569;
    }

    .meta-badge i {
        font-size: 10px;
    }

    .result-arrow {
        font-size: 14px;
        color: #cbd5e1;
        transition: all 0.2s;
    }

    .result-card:hover .result-arrow {
        color: #6366f1;
        transform: translateX(4px);
    }

    /* No Results */
    .no-results {
        text-align: center;
        padding: 80px 20px;
    }

    .no-results-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        background: #f8fafc;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .no-results-icon i {
        font-size: 36px;
        color: #cbd5e1;
    }

    .no-results-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .no-results-text {
        font-size: 16px;
        color: #64748b;
        margin-bottom: 24px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ss-container {
            padding: 0 20px;
        }

        .search-title {
            font-size: 28px;
        }

        .search-input {
            width: 200px;
        }

        .search-input:focus {
            width: 240px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
        
        const notifDropdown = document.getElementById('notificationDropdown');
        if (notifDropdown) notifDropdown.classList.remove('show');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
        
        const userDropdown = document.getElementById('userDropdown');
        if (userDropdown) userDropdown.classList.remove('show');
        
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
        }
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
    });

    window.addEventListener('load', function() {
        fetch('/notifications')
            .then(res => res.json())
            .then(data => updateNotificationBadge(data.unread_count))
            .catch(err => console.error('Error loading notification count:', err));
    });
</script>
@endpush
@endsection
