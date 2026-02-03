@extends('layouts.app')

@section('title', $solution->name . ' - Leverage Fluency')

@section('content')
<div class="container">
    <h1>{{ $solution->log->name }} → {{ $solution->name }}</h1>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <div class="card" style="margin-bottom: 24px;">
        <h2>Vấn Đề</h2>
        <pre>{{ $solution->log->content }}</pre>

        <h2>Giải Pháp Hiện Tại</h2>
        <pre>{{ $solution->content }}</pre>

        <!-- Nút mở Modal Cập Nhật -->
        <div style="text-align: center; margin-top: 30px;">
            <button id="openUpdateModal" class="btn">
                Cập Nhật Giải Pháp
            </button>
        </div>
    </div>

    <div class="card">
        <h2>Lịch Sử Thay Đổi (3 phiên bản gần nhất)</h2>
        <table>
            <tr>
                <th>Phiên Bản</th>
                <th>Trạng Thái</th>
                <th>Nội Dung (rút gọn)</th>
                <th>Thời Gian</th>
            </tr>
            @forelse ($solution->history as $h)
                <tr>
                    <td>{{ $h->version }}</td>
                    <td>{{ $h->status_label }}</td>
                    <td>{{ Str::limit($h->content, 150) }}</td>
                    <td>{{ $h->changed_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Chưa có thay đổi nào</td>
                </tr>
            @endforelse
        </table>
    </div>

    <div style="margin-top: 20px;">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Quay Lại Danh Sách</a>
    </div>
</div>

<!-- Modal Cập Nhật Giải Pháp -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Cập Nhật Giải Pháp</h2>
        <form method="POST" action="{{ route('solutions.update', $solution) }}">
            @csrf
            @method('PUT')

            <label>Tên giải pháp:</label>
            <input type="text" name="name" value="{{ $solution->name }}" required>

            <label>Nội dung:</label>
            <textarea name="content" rows="10" required>{{ $solution->content }}</textarea>

            <label>Phiên bản:</label>
            <input type="text" name="version" value="{{ $solution->version }}">

            <label>Trạng thái:</label>
            <select name="status">
                <option value="draft" {{ $solution->status == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                <option value="testing" {{ $solution->status == 'testing' ? 'selected' : '' }}>Đang kiểm tra</option>
                <option value="done" {{ $solution->status == 'done' ? 'selected' : '' }}>Hoàn thành</option>
            </select>

            <div style="margin-top: 20px;">
                <button type="submit">Lưu Thay Đổi</button>
                <button type="button" id="cancelUpdate" class="btn btn-secondary">Hủy</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const modal = document.getElementById("updateModal");
    const openBtn = document.getElementById("openUpdateModal");
    const closeBtn = modal ? modal.querySelector('.close') : null;
    const cancelBtn = document.getElementById("cancelUpdate");

    openBtn && openBtn.addEventListener('click', () => modal.style.display = 'block');
    closeBtn && closeBtn.addEventListener('click', () => modal.style.display = 'none');
    cancelBtn && cancelBtn.addEventListener('click', () => modal.style.display = 'none');

    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.style.display = 'none';
    });
</script>
@endpush
@endsection
