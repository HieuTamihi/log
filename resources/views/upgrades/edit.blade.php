@extends('layouts.app')

@section('title', 'Chỉnh Sửa Cải Tiến - System Sight')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    
    <!-- Header -->
    <x-navbar />

    <main class="ss-main">
        <div class="ss-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('dashboard') }}" class="breadcrumb-item">Hệ Thống Kinh Doanh</a>
                <span class="breadcrumb-separator">→</span>
                <a href="{{ route('machines.show', $upgrade->component->subsystem->machine->slug) }}" class="breadcrumb-item">{{ $upgrade->component->subsystem->machine->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <a href="{{ route('subsystems.show', ['machineSlug' => $upgrade->component->subsystem->machine->slug, 'subsystemSlug' => $upgrade->component->subsystem->slug]) }}" class="breadcrumb-item">{{ $upgrade->component->subsystem->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">Chỉnh Sửa Cải Tiến</span>
            </div>

            <!-- Page Header -->
            <div class="page-header-editor">
                <div class="header-icon status-{{ $upgrade->status }}">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h1 class="page-title-editor">{{ $upgrade->name }}</h1>
                    <p class="page-subtitle-editor">{{ $upgrade->component->name }} • {{ $upgrade->component->subsystem->name }}</p>
                </div>
                <div class="status-indicator status-{{ $upgrade->status }}">
                    @if($upgrade->status === 'shipped')
                        <i class="fas fa-check-circle"></i>
                        <span>{{ __('messages.shipped') }}</span>
                    @elseif($upgrade->status === 'active')
                        <i class="fas fa-bolt"></i>
                        <span>Active</span>
                    @else
                        <i class="fas fa-file-alt"></i>
                        <span>{{ __('messages.draft') }}</span>
                    @endif
                </div>
            </div>

            @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <!-- Upgrade Form -->
            <form action="{{ route('upgrades.update_post', $upgrade) }}" method="POST" class="upgrade-form">
                @csrf
                <!-- @method('PUT') temporarily removed for debugging -->

                <div class="form-card">
                    <div class="form-group">
                        <label for="name" class="form-label">Tên Phiên Bản / Tiêu Đề</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            placeholder="Ví dụ: Cập nhật nội dung v2"
                            required
                            value="{{ old('name', $upgrade->name) }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">Nội dung (Markdown)</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-textarea" 
                            rows="15"
                            placeholder="Viết nội dung note ở đây..."
                        >{{ old('content', $upgrade->content) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="purpose" class="form-label">Ghi Chú Thay Đổi (Optional)</label>
                        <textarea 
                            id="purpose" 
                            name="purpose" 
                            class="form-textarea" 
                            rows="2"
                            placeholder="Tóm tắt những thay đổi trong phiên bản này..."
                        >{{ old('purpose', $upgrade->purpose) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="trigger" class="form-label">Khi nào kích hoạt? (Optional)</label>
                        <textarea 
                            id="trigger" 
                            name="trigger" 
                            class="form-textarea" 
                            rows="2"
                            placeholder="Khi nào nên sử dụng quy trình này?"
                        >{{ old('trigger', $upgrade->trigger) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Các Bước</label>
                        <div id="steps-container">
                            @if($upgrade->steps && count($upgrade->steps) > 0)
                                @foreach($upgrade->steps as $index => $step)
                                <div class="step-item">
                                    <input 
                                        type="text" 
                                        name="steps[]" 
                                        class="form-input" 
                                        placeholder="Bước {{ $index + 1 }}"
                                        value="{{ $step }}"
                                    >
                                </div>
                                @endforeach
                            @else
                                <div class="step-item">
                                    <input 
                                        type="text" 
                                        name="steps[]" 
                                        class="form-input" 
                                        placeholder="Bước 1"
                                    >
                                </div>
                            @endif
                        </div>
                        <button type="button" onclick="addStep()" class="btn-add-step">
                            <i data-lucide="plus"></i>
                            <span>Thêm Bước</span>
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="definition_of_done" class="form-label">Tiêu Chí Hoàn Thành</label>
                        <textarea 
                            id="definition_of_done" 
                            name="definition_of_done" 
                            class="form-textarea" 
                            rows="3"
                            placeholder="Làm sao bạn biết cải tiến này đã hoàn thành?"
                        >{{ old('definition_of_done', $upgrade->definition_of_done) }}</textarea>
                    </div>
                </div>

                <!-- Hidden submit button for the main form -->
                <input type="hidden" id="main-form-submit-trigger">
            </form>

            <!-- Action Buttons Container -->
            <div class="action-buttons-row" style="display: flex; align-items: center; gap: 12px; margin-top: 24px;">
                <!-- Delete Form (left side) -->
                <form action="{{ route('upgrades.destroy', $upgrade) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa cải tiến này?');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <i class="fas fa-trash"></i>
                        <span>{{ __('messages.delete') }}</span>
                    </button>
                </form>
                
                <!-- Spacer -->
                <div style="flex: 1;"></div>
                
                <!-- Cancel Link -->
                <a href="{{ route('subsystems.show', ['machineSlug' => $upgrade->component->subsystem->machine->slug, 'subsystemSlug' => $upgrade->component->subsystem->slug]) }}" class="btn-secondary">
                    {{ __('messages.cancel') }}
                </a>
                
                <!-- Save Button (triggers main form) -->
                <button type="button" class="btn-primary" onclick="document.querySelector('.upgrade-form').submit();">
                    <i class="fas fa-save"></i>
                    <span>{{ __('messages.save') }}</span>
                </button>
            </div>

            @if($upgrade->status !== 'shipped')
            <form action="{{ route('upgrades.ship', $upgrade) }}" method="POST" class="ship-form" style="margin-top: 24px;">
                @csrf
                <button type="submit" class="btn-ship-large">
                    <i class="fas fa-rocket"></i>
                    <span>{{ __('messages.ship_this_upgrade') }}</span>
                </button>
            </form>
            @else
            <div class="shipped-badge">
                <i class="fas fa-check-circle"></i>
                <span>{{ __('messages.shipped_on') }} {{ $upgrade->shipped_at->format('d/m/Y') }}</span>
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
        max-width: 900px;
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
        max-width: 900px;
        margin: 0 auto;
        padding: 0 32px;
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

    .page-header-editor {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
    }

    .header-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .header-icon i {
        font-size: 24px;
        line-height: 1;
    }

    .header-icon.status-active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .page-title-editor {
        font-size: 28px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 2px;
    }

    .page-subtitle-editor {
        color: #64748b;
        font-size: 14px;
    }

    .alert-success {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        color: #065f46;
        font-weight: 500;
        margin-bottom: 24px;
    }

    .alert-success i {
        width: 20px;
        height: 20px;
    }

    .upgrade-form {
        margin-bottom: 24px;
    }

    .form-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        color: #1a202c;
        background: white;
        transition: all 0.2s;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-textarea {
        resize: vertical;
        font-family: inherit;
    }

    #steps-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 12px;
    }

    .step-item {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-add-step {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add-step:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .btn-secondary,
    .btn-primary,
    .btn-danger {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-danger {
        background: white;
        color: #ef4444;
        border: 1px solid #fecaca;
    }

    .btn-danger:hover {
        background: #fef2f2;
        border-color: #fca5a5;
    }

    .ship-form {
        margin-bottom: 40px;
    }

    .btn-ship-large {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        transition: all 0.3s;
    }

    .btn-ship-large i {
        width: 24px;
        height: 24px;
    }

    .btn-ship-large:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    .shipped-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 18px;
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        border-radius: 12px;
        color: #065f46;
        font-size: 16px;
        font-weight: 600;
    }

    .shipped-badge i {
        width: 24px;
        height: 24px;
    }

    /* Status Indicator in Header */
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-left: auto;
    }

    .status-indicator.status-draft {
        background: #f1f5f9;
        color: #64748b;
    }

    .status-indicator.status-active {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status-indicator.status-shipped {
        background: #d1fae5;
        color: #065f46;
    }

    .page-header-editor {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
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
        
        if (userMenu && dropdown && !userMenu.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    function addStep() {
        const container = document.getElementById('steps-container');
        const stepCount = container.children.length + 1;
        
        const stepItem = document.createElement('div');
        stepItem.className = 'step-item';
        stepItem.innerHTML = `
            <input 
                type="text" 
                name="steps[]" 
                class="form-input" 
                placeholder="Bước ${stepCount}"
            >
        `;
        
        container.appendChild(stepItem);
    }

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
@endpush
@endsection
