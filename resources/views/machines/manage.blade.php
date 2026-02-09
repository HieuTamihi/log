@extends('layouts.app')

@section('title', 'Manage Machines - System Sight')

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
                                    <stop offset="0%" stop-color="#6366f1"/>
                                    <stop offset="100%" stop-color="#a855f7"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <span class="logo-text">System Sight</span>
                </div>
                
                <div class="header-actions">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ __('messages.back_to_dashboard') }}</span>
                    </a>
                    
                    <div class="user-menu" onclick="toggleUserDropdown()">
                        <div class="user-avatar">{{ substr(Auth::user()->username, 0, 1) }}</div>
                    </div>
                    
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

    <main class="ss-main">
        <div class="ss-container">
            <div class="page-header-detail">
                <div>
                    <h1 class="page-title-detail">Manage Machines</h1>
                    <p class="page-subtitle">Create, edit, and organize your business machines</p>
                </div>
                <a href="{{ route('machines.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('messages.create_machine') }}</span>
                </a>
            </div>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            <div class="machines-list">
                @forelse($machines as $machine)
                <div class="machine-list-item">
                    <div class="machine-list-icon">{{ $machine->icon }}</div>
                    <div class="machine-list-info">
                        <h3>{{ $machine->name }}</h3>
                        <p>{{ $machine->description }}</p>
                        <div class="machine-list-meta">
                            <span>{{ $machine->subsystems->count() }} subsystems</span>
                            <span>•</span>
                            <span>{{ $machine->components->count() }} components</span>
                        </div>
                    </div>
                    <div class="machine-list-actions">
                        <a href="{{ route('machines.show', $machine) }}" class="btn-secondary btn-small">
                            <i class="fas fa-eye"></i>
                            <span>View</span>
                        </a>
                        <a href="{{ route('machines.edit', $machine) }}" class="btn-secondary btn-small">
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">🎯</div>
                    <h3>No machines yet</h3>
                    <p>Create your first business machine to get started</p>
                    <a href="{{ route('machines.create') }}" class="btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('messages.create_machine') }}</span>
                    </a>
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
        max-width: 1200px;
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
        gap: 12px;
        position: relative;
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

    .page-header-detail {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
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

    .btn-small {
        padding: 8px 14px;
        font-size: 13px;
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-weight: 500;
        font-size: 14px;
    }

    .alert.success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .machines-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .machine-list-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.2s;
    }

    .machine-list-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .machine-list-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .machine-list-info {
        flex: 1;
    }

    .machine-list-info h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .machine-list-info p {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .machine-list-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #94a3b8;
    }

    .machine-list-actions {
        display: flex;
        gap: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
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
</script>
@endpush
@endsection
