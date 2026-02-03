@extends('layouts.app')

@section('title', 'Danh sách vấn đề - Leverage Fluency')

@section('content')
<div class="container">
    <div class="user-info">
        Xin chào <strong>{{ Auth::user()->username }}</strong> |
        <a href="{{ route('dashboard') }}" style="color: #60a5fa;">← Trang chủ</a> |
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
    <h1 class="page-title">Danh sách vấn đề</h1>

    <!-- Stats Dashboard -->
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

    <!-- Slider Tabs -->
    <div class="tabs">
        <button class="tablink active" onclick="openTab(event,'all')">Tất cả Logs</button>
        <button class="tablink" onclick="openTab(event,'pending')">Chưa Giải Quyết</button>
        <button class="tablink" onclick="openTab(event,'inprogress')">Solution Đang Làm</button>
        <button class="tablink" onclick="openTab(event,'done')">Solution Hoàn Thành</button>
    </div>

    <!-- Tab Tất cả Logs -->
    <div id="all" class="tabcontent" style="display: block;">
        @forelse ($logs as $log)
            @include('components.log-item', ['log' => $log])
        @empty
            <p style="text-align: center; color: var(--text-muted);">Chưa có vấn đề nào.</p>
        @endforelse
    </div>

    <!-- Tab Đang Giải Quyết (chưa có solution) -->
    <div id="pending" class="tabcontent">
        @foreach ($logs as $log)
            @if (!$log->solution)
                @include('components.log-item', ['log' => $log])
            @endif
        @endforeach
    </div>

    <!-- Tab Solution Đang Làm -->
    <div id="inprogress" class="tabcontent">
        @foreach ($logs as $log)
            @if ($log->solution && $log->solution->status !== 'done')
                @include('components.log-item', ['log' => $log])
            @endif
        @endforeach
    </div>

    <!-- Tab Solution Hoàn Thành -->
    <div id="done" class="tabcontent">
        @foreach ($logs as $log)
            @if ($log->solution && $log->solution->status === 'done')
                @include('components.log-item', ['log' => $log])
            @endif
        @endforeach
    </div>

    <!-- Pagination -->
    <div style="text-align:center; margin:20px 0;">
        {{ $logs->links() }}
    </div>

    <!-- Modal Xem Nội Dung Đầy Đủ -->
    <div id="contentModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeContentModal">&times;</span>
            <h2>Nội Dung Chi Tiết Vấn Đề</h2>
            <pre id="fullContentDisplay"
                style="background:#f8f9fa; padding:20px; border-radius:8px; max-height:60vh; overflow-y:auto;"></pre>
        </div>
    </div>

    <!-- Edit Log Modal -->
    <div id="editLogModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Chỉnh sửa vấn đề</h2>
            <form method="POST" id="editLogForm">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 20px;">
                    <label for="edit_log_content" style="display:block; margin-bottom:8px; font-weight:bold;">Mô tả</label>
                    <textarea name="log_content" id="edit_log_content" rows="6" class="big-textarea"
                        style="border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius); background: var(--input-bg);"></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:bold;">Mức độ khó chịu?</label>
                    <input type="hidden" name="emotion_level" id="edit_emotion_level" value="">
                    <div class="emotion-selector" id="editEmotionGroup"
                        style="margin: 0; justify-content: flex-start; gap: 10px;">
                        <div class="emotion-option" onclick="selectEditEmotion(this, 'frustrated')"
                            id="edit_opt_frustrated" style="min-width: auto; padding: 10px;">
                            <span class="emotion-emoji" style="font-size: 24px;">😠</span>
                            <span class="emotion-label">Rất khó chịu</span>
                        </div>
                        <div class="emotion-option" onclick="selectEditEmotion(this, 'annoyed')"
                            id="edit_opt_annoyed" style="min-width: auto; padding: 10px;">
                            <span class="emotion-emoji" style="font-size: 24px;">😕</span>
                            <span class="emotion-label">Hơi khó chịu</span>
                        </div>
                        <div class="emotion-option" onclick="selectEditEmotion(this, 'neutral')"
                            id="edit_opt_neutral" style="min-width: auto; padding: 10px;">
                            <span class="emotion-emoji" style="font-size: 24px;">😐</span>
                            <span class="emotion-label">Bình thường</span>
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()"
                        style="margin-right: 10px;">Hủy</button>
                    <button type="submit" class="btn">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openTab(evt, tabName) {
        document.querySelectorAll(".tabcontent").forEach(t => t.style.display = "none");
        document.querySelectorAll(".tablink").forEach(t => t.classList.remove("active"));
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.classList.add("active");
    }

    // Modal for content preview
    const contentModal = document.getElementById("contentModal");
    const fullContentDisplay = document.getElementById("fullContentDisplay");
    const closeContent = contentModal ? contentModal.querySelector('.close') : null;

    document.querySelectorAll('.content-preview').forEach(item => {
        item.addEventListener('click', function () {
            fullContentDisplay.textContent = this.getAttribute('data-full');
            contentModal.style.display = 'block';
        });
    });

    closeContent && closeContent.addEventListener('click', () => contentModal.style.display = 'none');

    // Edit Modal Functions
    function openEditModal(id, content) {
        document.getElementById('editLogForm').action = `/logs/${id}`;

        // Parse emotion from content
        let cleanContent = content;
        let foundEmotion = '';

        const labelToKey = {
            'Rất khó chịu': 'frustrated',
            'Hơi khó chịu': 'annoyed',
            'Bình thường': 'neutral'
        };

        const match = content.match(/^\[(.*?)\]\s/);
        if (match && match[1]) {
            const label = match[1];
            if (labelToKey[label]) {
                foundEmotion = labelToKey[label];
                cleanContent = content.substring(match[0].length);
            }
        }

        document.getElementById('edit_log_content').value = cleanContent;
        document.getElementById('edit_emotion_level').value = foundEmotion;

        // Update UI selection
        document.querySelectorAll('#editEmotionGroup .emotion-option').forEach(el => el.classList.remove('selected'));
        if (foundEmotion) {
            const el = document.getElementById('edit_opt_' + foundEmotion);
            if (el) el.classList.add('selected');
        }

        document.getElementById('editLogModal').style.display = 'block';
    }

    function selectEditEmotion(el, value) {
        document.querySelectorAll('#editEmotionGroup .emotion-option').forEach(opt => opt.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('edit_emotion_level').value = value;
    }

    function closeEditModal() {
        document.getElementById('editLogModal').style.display = 'none';
    }

    // Close edit modal when clicking outside
    window.addEventListener('click', function (event) {
        const editModal = document.getElementById('editLogModal');
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
        if (event.target == contentModal) {
            contentModal.style.display = 'none';
        }
    });
</script>
@endpush
@endsection
