@extends('layouts.app')

@section('title', 'Tạo Giải Pháp - Leverage Fluency')

@section('body-class', 'form-page')

@section('content')
<div class="form-wrapper">
    <div class="form-card">
        <h1>Tạo Giải Pháp Mới</h1>

        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('solutions.store') }}">
            @csrf
            
            <label>Chọn vấn đề:</label>
            <select name="log_id" required>
                <option value="">-- Chọn vấn đề --</option>
                @foreach ($logsWithoutSolution as $log)
                    <option value="{{ $log->id }}" {{ $logId == $log->id ? 'selected' : '' }}>
                        {{ $log->id }} - {{ $log->name }}
                    </option>
                @endforeach
            </select>

            <label>Tên giải pháp:</label>
            <input type="text" name="solution_name" placeholder="Tên giải pháp" required value="{{ old('solution_name') }}">

            <label>Nội dung:</label>
            <textarea name="solution_content" rows="6" placeholder="Nội dung giải pháp chi tiết" required>{{ old('solution_content') }}</textarea>

            <label>Phiên bản:</label>
            <input type="text" name="solution_version" value="{{ old('solution_version', '1.0') }}" placeholder="Version">

            <label>Trạng thái:</label>
            <select name="solution_status">
                <option value="draft">Bản nháp</option>
                <option value="testing">Đang kiểm tra</option>
                <option value="done">Hoàn thành</option>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn">Tạo Giải Pháp</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Quay Lại</a>
            </div>
        </form>
    </div>
</div>
@endsection
