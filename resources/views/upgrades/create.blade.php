@extends('layouts.app')

@section('title', 'Tạo Cải Tiến Mới - System Sight')

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
                <a href="{{ route('machines.show', $component->subsystem->machine->slug) }}" class="breadcrumb-item">{{ $component->subsystem->machine->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <a href="{{ route('subsystems.show', ['machineSlug' => $component->subsystem->machine->slug, 'subsystemSlug' => $component->subsystem->slug]) }}" class="breadcrumb-item">{{ $component->subsystem->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">Tạo Cải Tiến</span>
            </div>

            <!-- Page Header -->
            <div class="page-header-editor">
                <div class="header-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h1 class="page-title-editor">{{ __('messages.ship_new_upgrade') }}</h1>
                    <p class="page-subtitle-editor">{{ $component->name }} • {{ $component->subsystem->name }}</p>
                </div>
            </div>

            <!-- Upgrade Form -->
            <form action="{{ route('upgrades.store') }}" method="POST" class="upgrade-form">
                @csrf
                <input type="hidden" name="component_id" value="{{ $component->id }}">

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
                            value="{{ old('name') }}"
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
                        >{{ old('content', $component->content) }}</textarea>
                        <p style="font-size: 12px; color: #64748b; margin-top: 4px;">Hỗ trợ Markdown cơ bản.</p>
                    </div>

                    <div class="form-group">
                        <label for="purpose" class="form-label">Ghi Chú Thay Đổi (Optional)</label>
                        <textarea 
                            id="purpose" 
                            name="purpose" 
                            class="form-textarea" 
                            rows="2"
                            placeholder="Tóm tắt những thay đổi trong phiên bản này..."
                        >{{ old('purpose') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="trigger" class="form-label">Khi nào kích hoạt? (Optional)</label>
                        <textarea 
                            id="trigger" 
                            name="trigger" 
                            class="form-textarea" 
                            rows="2"
                            placeholder="Khi nào nên sử dụng quy trình này?"
                        >{{ old('trigger') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Các Bước</label>
                        <div id="steps-container">
                            <div class="step-item">
                                <input 
                                    type="text" 
                                    name="steps[]" 
                                    class="form-input" 
                                    placeholder="Bước 1"
                                >
                            </div>
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
                        >{{ old('definition_of_done') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('subsystems.show', ['machineSlug' => $component->subsystem->machine->slug, 'subsystemSlug' => $component->subsystem->slug]) }}" class="btn-secondary">
                        {{ __('messages.cancel') }}
                    </a>
                    <div style="flex: 1;"></div>
                    <button type="submit" name="status" value="draft" class="btn-draft">
                        <i class="fas fa-save"></i>
                        <span>{{ __('messages.draft') }}</span>
                    </button>
                    <button type="submit" name="status" value="active" class="btn-primary">
                        <i class="fas fa-bolt"></i>
                        <span>{{ __('messages.create_upgrade') }}</span>
                    </button>
                </div>
            </form>

            <!-- Tips Sidebar -->
            <div class="tips-sidebar">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Mẹo viết Upgrade hiệu quả</h4>
                    <ul class="tip-list">
                        <li>Đặt tên ngắn gọn, dễ nhớ</li>
                        <li>Mục đích cần rõ ràng và cụ thể</li>
                        <li>Các bước nên đơn giản, không quá 5 bước</li>
                        <li>Tiêu chí hoàn thành phải đo lường được</li>
                    </ul>
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
        justify-content: center;
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

    .upgrade-form {
        margin-bottom: 40px;
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
        justify-content: flex-end;
    }

    .btn-secondary,
    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        width: auto;
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

    .btn-draft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        width: auto;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-draft:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* Tips Sidebar */
    .tips-sidebar {
        margin-top: 32px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .tip-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .tip-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: #92400e;
        font-size: 18px;
    }

    .tip-card h4 {
        font-size: 16px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 12px;
    }

    .tip-list {
        list-style: none;
        padding: 0;
    }

    .tip-list li {
        position: relative;
        padding-left: 20px;
        margin-bottom: 8px;
        font-size: 14px;
        color: #64748b;
    }

    .tip-list li::before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #10b981;
        font-weight: 600;
    }

    .coach-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px dashed #cbd5e1;
    }

    .coach-icon {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4f46e5;
    }

    .coach-desc {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 12px;
    }

    .coming-soon-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    @media (max-width: 768px) {
        .ss-container {
             padding: 0 16px;
        }

        .ss-main {
            padding: 24px 0 40px;
        }

        .page-header-editor {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }

        .form-actions {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .form-actions .btn-primary,
        .form-actions .btn-secondary,
        .form-actions .btn-draft {
            width: 100%;
            justify-content: center;
        }

        .tips-sidebar {
            display: none;
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
