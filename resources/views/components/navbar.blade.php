{{-- System Sight Navbar Component --}}
@push('styles')
<style>
    .ss-header {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        position: sticky;
        top: 0;
        z-index: 100;
        padding: 16px 0;
    }

    .ss-header .ss-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 32px;
    }

    @media (max-width: 768px) {
        .ss-header .ss-container {
            padding: 0 16px;
        }
        
        .ss-header .logo-text {
            display: none;
        }

        .ss-header .search-input {
            width: 120px !important;
            padding-right: 8px;
        }
        
        .ss-header .search-input:focus {
            width: 140px !important;
        }

        .ss-header .lang-text {
            display: none;
        }
    }

    .ss-header .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ss-header .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 18px;
        color: #1a202c;
        text-decoration: none;
    }

    .ss-header .logo-text {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .ss-header .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ss-header .search-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    .ss-header .search-icon {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    .ss-header .search-input {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 12px 8px 36px;
        font-size: 14px;
        width: 180px;
        transition: all 0.2s;
        outline: none;
    }

    .ss-header .search-input:focus {
        border-color: #6366f1;
        width: 220px;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .ss-header .language-switcher {
        display: flex;
        align-items: center;
    }

    .ss-header .lang-btn {
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

    .ss-header .lang-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
        color: #6366f1;
    }

    .ss-header .lang-btn .flag {
        font-size: 18px;
        line-height: 1;
    }

    .ss-header .lang-btn .lang-text {
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .ss-header .icon-btn {
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

    .ss-header .icon-btn i {
        font-size: 18px;
    }

    .ss-header .icon-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #6366f1;
    }

    .ss-header .notification-wrapper {
        position: relative;
    }

    .ss-header .notification-badge {
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

    .ss-header .notification-dropdown {
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
        z-index: 1000;
    }

    .ss-header .notification-dropdown.show {
        display: block;
    }

    .ss-header .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .ss-header .notification-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1a202c;
        margin: 0;
    }

    .ss-header .btn-mark-all-read {
        display: flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ss-header .btn-mark-all-read:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }

    .ss-header .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .ss-header .notification-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .ss-header .user-menu {
        position: relative;
        padding: 3px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
    }

    .ss-header .user-avatar {
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

    .ss-header .user-dropdown {
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

    .ss-header .user-dropdown.show {
        display: block;
    }

    .ss-header .dropdown-item {
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

    .ss-header .dropdown-item:hover {
        background: #fef2f2;
    }
</style>
@endpush

<header class="ss-header">
    <div class="ss-container">
        <div class="header-content">
            <a href="{{ route('dashboard') }}" class="logo">
                <div class="logo-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="8" fill="url(#logo-gradient-nav)"/>
                        <text x="16" y="22" text-anchor="middle" fill="white" font-size="16" font-weight="700">Ss</text>
                        <defs>
                            <linearGradient id="logo-gradient-nav" x1="0" y1="0" x2="32" y2="32">
                                <stop offset="0%" stop-color="#667eea"/>
                                <stop offset="100%" stop-color="#764ba2"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <span class="logo-text">System Sight</span>
            </a>
            
            <div class="header-actions">
                <form action="{{ route('search') }}" method="GET" class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="search" name="q" placeholder="{{ __('messages.search') }}" class="search-input" value="{{ request('q') }}">
                </form>
                
                {{-- Notification --}}
                <div class="notification-wrapper">
                    <button class="icon-btn notification-btn" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                    </button>
                    
                    {{-- Notification Dropdown --}}
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h3>{{ __('messages.notifications') }}</h3>
                            <button class="btn-mark-all-read" onclick="markAllAsRead()">
                                <i class="fas fa-check-double"></i>
                                <span>{{ __('messages.mark_all_read') }}</span>
                            </button>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <div class="notification-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>{{ __('messages.loading') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- User Menu --}}
                <div class="user-menu" onclick="toggleUserDropdown()">
                    <div class="user-avatar">{{ substr(Auth::user()->username, 0, 1) }}</div>
                </div>
                
                {{-- User Dropdown --}}
                <div class="user-dropdown" id="userDropdown">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>{{ __('messages.logout') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu');
        const userDropdown = document.getElementById('userDropdown');
        const notificationBtn = document.querySelector('.notification-btn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (userMenu && userDropdown && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove('show');
        }
        
        if (notificationBtn && notificationDropdown && !notificationBtn.contains(event.target) && !notificationDropdown.contains(event.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
</script>
@endpush
