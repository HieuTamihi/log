@extends('layouts.app')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    
    <!-- Header -->
    <x-navbar />

    <main class="ss-main">
        <div class="ss-container">
            <div class="mb-6">
                <a href="{{ route('subsystems.show', [$component->subsystem->machine->slug, $component->subsystem->slug]) }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('messages.back_to_subsystem') }}</span>
                </a>
            </div>

            <div class="form-container">
                <h1 class="form-title">{{ __('messages.edit_component') }}</h1>

                <form action="{{ route('components.update', $component) }}" method="POST" class="form-content">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">
                            {{ __('messages.name') }} *
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $component->name) }}"
                               required>
                        @error('name')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">
                            {{ __('messages.description') }} *
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  required>{{ old('description', $component->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="health_status">
                            {{ __('messages.health_status') }} *
                        </label>
                        <select id="health_status" name="health_status" required>
                            <option value="green" {{ old('health_status', $component->health_status) == 'green' ? 'selected' : '' }}>
                                🟢 {{ __('messages.smooth') }}
                            </option>
                            <option value="yellow" {{ old('health_status', $component->health_status) == 'yellow' ? 'selected' : '' }}>
                                🟡 {{ __('messages.needs_love') }}
                            </option>
                            <option value="red" {{ old('health_status', $component->health_status) == 'red' ? 'selected' : '' }}>
                                🔴 {{ __('messages.on_fire') }}
                            </option>
                        </select>
                        @error('health_status')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="current_issue">
                            {{ __('messages.current_issue') }}
                        </label>
                        <input type="text" 
                               id="current_issue" 
                               name="current_issue" 
                               value="{{ old('current_issue', $component->current_issue) }}"
                               placeholder="Ví dụ: Hook cảm thấy cũ kỹ">
                        @error('current_issue')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="metric_value">
                                {{ __('messages.metric_value') }}
                                <span class="help-icon">
                                    ?
                                    <span class="tooltip-text">Con số đo lường hiệu suất hiện tại (Ví dụ: 80, 1000)</span>
                                </span>
                            </label>
                            <input type="number" 
                                   id="metric_value" 
                                   name="metric_value" 
                                   value="{{ old('metric_value', $component->metric_value) }}"
                                   placeholder="5">
                            @error('metric_value')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="metric_label">
                                {{ __('messages.metric_label') }}
                                <span class="help-icon">
                                    ?
                                    <span class="tooltip-text">Đơn vị hoặc tên chỉ số đo lường (Ví dụ: %, lượt xem, độ hài lòng)</span>
                                </span>
                            </label>
                            <input type="text" 
                                   id="metric_label" 
                                   name="metric_label" 
                                   value="{{ old('metric_label', $component->metric_label) }}"
                                   placeholder="Hooks">
                            @error('metric_label')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="order">
                            {{ __('messages.order') }} (Thứ tự hiển thị)
                        </label>
                        <input type="number" 
                               id="order" 
                               name="order" 
                               value="{{ old('order', $component->order) }}"
                               placeholder="VD: 1, 2, 3...">
                        <p class="text-sm text-gray-500 mt-1">Số nhỏ hơn sẽ hiển thị trước. Để trống sẽ tự động xếp cuối.</p>
                        @error('order')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i>
                            <span>{{ __('messages.update') }}</span>
                        </button>
                        <a href="{{ route('subsystems.show', [$component->subsystem->machine->slug, $component->subsystem->slug]) }}" class="btn-secondary">
                            <span>{{ __('messages.cancel') }}</span>
                        </a>
                    </div>
                </form>

                <div class="delete-section">
                    <form action="{{ route('components.destroy', $component) }}" method="POST" 
                          onsubmit="return confirm('{{ __('messages.confirm_delete_component') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash"></i>
                            <span>{{ __('messages.delete_component') }}</span>
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

    /* Tooltip Styles */
    .help-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        background: #e2e8f0;
        color: #64748b;
        border-radius: 50%;
        font-size: 12px;
        margin-left: 6px;
        cursor: help;
        position: relative;
        vertical-align: middle;
    }

    .help-icon:hover {
        background: #cbd5e1;
        color: #334155;
    }

    .help-icon .tooltip-text {
        visibility: hidden;
        width: 220px;
        background-color: #1e293b;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 8px 12px;
        position: absolute;
        z-index: 10;
        bottom: 135%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;
        font-weight: 400;
        font-size: 12px;
        line-height: 1.4;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        text-transform: none;
        pointer-events: none;
    }

    .help-icon .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1e293b transparent transparent transparent;
    }

    .help-icon:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
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
    textarea,
    select {
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
    textarea:focus,
    select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    textarea {
        min-height: 100px;
        resize: vertical;
    }

    .text-red-500 {
        color: #ef4444;
        font-size: 13px;
        margin-top: 4px;
    }

    .text-sm {
        font-size: 13px;
    }

    .text-gray-500 {
        color: #64748b;
    }

    .mt-1 {
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

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@endsection
