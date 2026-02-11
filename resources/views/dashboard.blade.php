@extends('layouts.app')

@section('title', 'System Sight - Business Machine')

@section('content')
<div class="container">
    <div class="user-info">
        Xin chào <strong>{{ Auth::user()->username }}</strong> |
        <a href="#" onclick="forceReload(); return false;" style="color: #60a5fa;">🔄 Làm mới</a> |
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #60a5fa; cursor: pointer; padding: 0; font-size: inherit;">Đăng xuất</button>
        </form>
    </div>

    <!-- Thông báo -->
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <!-- Page Header -->
    <h1 class="page-title">System Sight</h1>
    <p class="page-subtitle">See it. Build it. Improve it.</p>

    @if ($countLogged > 0)
        <!-- Stats Dashboard - Only show if there are problems -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number">{{ $countLogged }}</span>
                <span class="stat-label">Logged</span>
            </div>
            <div class="stat-card">
                <span class="stat-number warning">{{ $countInProgress }}</span>
                <span class="stat-label">Recurring</span>
            </div>
            <div class="stat-card">
                <span class="stat-number danger">{{ $countNeedAction }}</span>
                <span class="stat-label">Need action</span>
            </div>
        </div>
    @endif

    <!-- Zoom Controls -->
    @if($machines->count() > 0)
    <div class="zoom-controls">
        <button onclick="zoomOut()" class="zoom-btn" id="zoomOutBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
            Toàn cảnh
        </button>
        <button onclick="zoomIn()" class="zoom-btn active" id="zoomInBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
            Chi tiết
        </button>
    </div>

    <!-- System Overview - Zoom Out View -->
    <div id="zoomOutView" class="system-overview" style="display: none;">
        <div class="system-map">
            @foreach($machines as $machine)
            <div class="machine-node" onclick="window.location.href='{{ route('machines.show', $machine->slug) }}'">
                <div class="machine-header">
                    <span class="machine-icon">{{ $machine->icon ?? '⚙️' }}</span>
                    <h3>{{ $machine->name }}</h3>
                </div>
                <div class="subsystems-mini">
                    @foreach($machine->subsystems as $subsystem)
                    <div class="subsystem-mini" title="{{ $subsystem->name }}">
                        <span class="subsystem-mini-icon">{{ $subsystem->icon ?? '📦' }}</span>
                        <span class="subsystem-mini-name">{{ Str::limit($subsystem->name, 15) }}</span>
                        <span class="health-dot health-{{ $subsystem->health_status }}"></span>
                    </div>
                    @endforeach
                </div>
                <div class="machine-stats">
                    <span>{{ $machine->subsystems->count() }} subsystems</span>
                    <span>{{ $machine->components->count() }} components</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Zoom In View - Simple List -->
    <div id="zoomInView" class="machines-list">
        @foreach($machines as $machine)
        <a href="{{ route('machines.show', $machine->slug) }}" class="machine-card">
            <div class="machine-card-header">
                <span class="machine-card-icon">{{ $machine->icon ?? '⚙️' }}</span>
                <div>
                    <h3 class="machine-card-title">{{ $machine->name }}</h3>
                    <p class="machine-card-desc">{{ Str::limit($machine->description, 60) }}</p>
                </div>
            </div>
            <div class="machine-card-footer">
                <span class="badge">{{ $machine->subsystems->count() }} subsystems</span>
                <span class="health-badge health-{{ $machine->health_status }}">
                    {{ ucfirst(str_replace('_', ' ', $machine->health_status)) }}
                </span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <!-- Main Content Area -->
    <div class="main-content-area">
        <!-- Hero Button - Tạo vấn đề mới -->
        <button onclick="openWizard()" class="hero-btn-inline">
            + Tạo vấn đề
        </button>

        @if ($countLogged > 0)
            <!-- View Problems Button -->
            <a href="{{ route('logs.index') }}" class="view-problems-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                Xem vấn đề ({{ $countLogged }})
            </a>
        @endif
    </div>

    <!-- Wizard Overlay - Lovable Style Multi-Step -->
    <div id="addLogWizard" class="wizard-overlay">
        <div class="wizard-container">
            <!-- Close button -->
            <button class="wizard-close-btn" onclick="closeWizard()">✕</button>

            <form method="POST" action="{{ route('logs.store') }}" id="wizardForm">
                @csrf
                <!-- Hidden fields -->
                <input type="hidden" name="log_version" value="1.0">
                <input type="hidden" name="log_status" id="hiddenStatus" value="open">
                <input type="hidden" name="log_name" id="hiddenLogName" value="Vấn đề mới">
                <input type="hidden" name="emotion_level" id="hiddenEmotionLevel" value="">

                <!-- Debug: Show form action -->
                <script>
                    console.log('Form action:', '{{ route('logs.store') }}');
                    console.log('CSRF token:', '{{ csrf_token() }}');
                </script>

                <!-- Step 1: Mô tả vấn đề -->
                <div class="wizard-step active" id="step1">
                    <h2 class="wizard-question">Mô tả vấn đề</h2>
                    <p class="wizard-hint">Ghi lại điều gì đang xảy ra</p>

                    <textarea name="log_content" class="big-textarea" placeholder="Ví dụ: Lại quên mật khẩu wifi..."
                        required></textarea>

                    <div class="wizard-actions">
                        <button type="button" class="btn" onclick="goToStep(2)">Tiếp tục</button>
                    </div>
                </div>

                <!-- Step 2: Mức độ khó chịu -->
                <div class="wizard-step" id="step2">
                    <h2 class="wizard-question">Mức độ khó chịu?</h2>
                    <p class="wizard-hint">Chọn để lưu vấn đề</p>

                    <div class="emotion-selector" id="emotionGroup">
                        <div class="emotion-option" onclick="selectEmotionAndSubmit('frustrated')">
                            <span class="emotion-emoji">😠</span>
                            <span class="emotion-label">Rất khó chịu</span>
                        </div>
                        <div class="emotion-option" onclick="selectEmotionAndSubmit('annoyed')">
                            <span class="emotion-emoji">😕</span>
                            <span class="emotion-label">Hơi khó chịu</span>
                        </div>
                        <div class="emotion-option" onclick="selectEmotionAndSubmit('neutral')">
                            <span class="emotion-emoji">😐</span>
                            <span class="emotion-label">Bình thường</span>
                        </div>
                    </div>

                    <div class="wizard-actions">
                        <span class="wizard-back" onclick="goToStep(1)">Quay lại</span>
                    </div>
                </div>

                <!-- Hidden submit button -->
                <button type="submit" id="hiddenSubmit" style="display:none;"></button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Zoom functionality
    let isZoomedOut = false;

    function zoomOut() {
        isZoomedOut = true;
        document.getElementById('zoomOutView').style.display = 'block';
        document.getElementById('zoomInView').style.display = 'none';
        document.getElementById('zoomOutBtn').classList.add('active');
        document.getElementById('zoomInBtn').classList.remove('active');
    }

    function zoomIn() {
        isZoomedOut = false;
        document.getElementById('zoomOutView').style.display = 'none';
        document.getElementById('zoomInView').style.display = 'block';
        document.getElementById('zoomInBtn').classList.add('active');
        document.getElementById('zoomOutBtn').classList.remove('active');
    }

    // Wizard Logic
    const wizard = document.getElementById("addLogWizard");
    let currentStep = 1;

    function openWizard() {
        wizard.style.display = 'block';
        currentStep = 1;
        resetWizard();
    }

    function closeWizard() {
        wizard.style.display = 'none';
        resetWizard();
    }

    function resetWizard() {
        // Reset all steps
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            step.classList.remove('active', 'step-exit-left', 'step-enter-right');
            step.style.display = index === 0 ? 'block' : 'none';
            if (index === 0) step.classList.add('active');
        });
        // Reset selections
        document.querySelectorAll('.emotion-option').forEach(opt => opt.classList.remove('selected'));
        // Reset form
        document.getElementById('wizardForm').reset();
        document.getElementById('hiddenEmotionLevel').value = '';
        currentStep = 1;
    }

    function goToStep(stepNum) {
        const currentStepEl = document.getElementById(`step${currentStep}`);
        const nextStepEl = document.getElementById(`step${stepNum}`);

        // Validation for step 1 (description required)
        if (currentStep === 1 && stepNum > 1) {
            const content = document.querySelector('textarea[name="log_content"]').value;
            if (!content.trim()) {
                alert("Vui lòng nhập mô tả vấn đề!");
                return;
            }
        }

        // Animate out
        currentStepEl.classList.add("step-exit-left");

        setTimeout(() => {
            currentStepEl.classList.remove("active", "step-exit-left");
            currentStepEl.style.display = "none";

            nextStepEl.style.display = "block";
            nextStepEl.classList.add("step-enter-right", "active");
            currentStep = stepNum;
        }, 250);
    }

    // Emotion Selection - Auto submit
    function selectEmotionAndSubmit(value) {
        console.log('Emotion selected:', value);
        
        // Visual feedback
        event.currentTarget.classList.add('selected');
        document.getElementById('hiddenEmotionLevel').value = value;

        // Map emotion to status
        const statusMap = {
            'frustrated': 'open',
            'annoyed': 'in_progress',
            'neutral': 'in_progress'
        };
        document.getElementById('hiddenStatus').value = statusMap[value] || 'open';

        // Debug: Check form data
        const formData = new FormData(document.getElementById('wizardForm'));
        console.log('Form data before submit:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }

        // Submit form after brief animation
        setTimeout(() => {
            console.log('Submitting form...');
            document.getElementById('wizardForm').submit();
        }, 400);
    }

    // Force Refresh Clear Cache
    async function forceReload() {
        const btn = event.target;
        btn.innerHTML = "🔄 Đang xử lý...";

        try {
            // 1. Unregister Service Workers
            if ('serviceWorker' in navigator) {
                const registrations = await navigator.serviceWorker.getRegistrations();
                for (let registration of registrations) {
                    await registration.unregister();
                }
            }

            // 2. Xóa Cache Storage
            if ('caches' in window) {
                const cacheNames = await caches.keys();
                await Promise.all(
                    cacheNames.map(name => caches.delete(name))
                );
            }

            console.log("Cache cleared!");
        } catch (e) {
            console.error("Error clearing cache:", e);
        }

        // 3. Reload trang cực mạnh (bỏ qua cache trình duyệt)
        window.location.href = window.location.pathname + '?t=' + new Date().getTime();
    }

    // Close wizard on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && wizard.style.display === 'block') {
            closeWizard();
        }
    });
</script>
@endpush
@endsection
