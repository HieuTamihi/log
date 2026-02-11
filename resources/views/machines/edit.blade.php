@extends('layouts.app')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    
    <!-- Header -->
    <x-navbar />

    <main class="ss-main">
        <div class="ss-container">
            <div class="mb-6">
                <a href="{{ route('machines.show', $machine) }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('messages.back_to_machine') }}</span>
                </a>
            </div>

            <div class="form-container">
                <h1 class="form-title">{{ __('messages.edit_machine') }}</h1>

                <form action="{{ route('machines.update', $machine) }}" method="POST" class="form-content">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">Header *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $machine->name) }}" required>
                        @error('name')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sub_header">Sub-header</label>
                        <input type="text" id="sub_header" name="sub_header" value="{{ old('sub_header', $machine->sub_header ?? $machine->description) }}">
                        @error('sub_header')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="detail_description">Detail Description</label>
                        <textarea id="detail_description" name="detail_description" rows="5">{{ old('detail_description', $machine->detail_description) }}</textarea>
                        @error('detail_description')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i>
                            <span>{{ __('messages.update') }}</span>
                        </button>
                        <a href="{{ route('machines.show', $machine) }}" class="btn-secondary">
                            <span>{{ __('messages.cancel') }}</span>
                        </a>
                    </div>
                </form>

                <div class="delete-section">
                    <form action="{{ route('machines.destroy', $machine) }}" method="POST" 
                          onsubmit="return confirm('{{ __('messages.confirm_delete_machine') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash"></i>
                            <span>{{ __('messages.delete_machine') }}</span>
                        </button>
                    </form>
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
        max-width: 800px;
        margin: 0 auto;
        padding: 0 32px;
    }

    .ss-main {
        padding: 40px 0 80px;
    }

    .mb-6 {
        margin-bottom: 24px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6366f1;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: #4f46e5;
        gap: 12px;
    }

    .form-container {
        background: white;
        border-radius: 16px;
        padding: 40px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .form-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 32px;
    }

    .form-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
    }

    input[type="text"],
    input[type="number"],
    input[type="color"],
    textarea {
        width: 100%;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #1a202c;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.2s;
        outline: none;
    }

    input:focus,
    textarea:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    textarea {
        min-height: 100px;
        resize: vertical;
    }

    input[type="color"] {
        height: 48px;
        cursor: pointer;
        padding: 4px;
    }

    .text-red-500 {
        color: #ef4444;
        font-size: 13px;
        margin-top: 4px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 8px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
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
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
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

    .delete-section {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
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
</script>
@endpush
@endsection
