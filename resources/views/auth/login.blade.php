@extends('layouts.app')

@section('title', 'Đăng Nhập - Leverage Fluency')

@section('body-class', 'auth-page')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Đăng Nhập</h1>
        <p class="auth-subtitle">
            Đăng nhập để quản lý Vấn Đề & Giải Pháp
        </p>

        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="text" name="username" placeholder="Tên đăng nhập" required autofocus value="{{ old('username') }}">
            <input type="password" name="password" placeholder="Mật khẩu" required>

            <div style="display: flex; align-items: center; margin-bottom: 16px; justify-content: flex-start;">
                <input type="checkbox" name="remember" id="remember"
                    style="width: auto; margin-right: 8px; margin-bottom: 0;">
                <label for="remember"
                    style="margin-bottom: 0; cursor: pointer; color: var(--text-secondary); text-transform: none;">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit" class="btn auth-btn">Đăng Nhập</button>
        </form>

        <p class="auth-link">
            Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
        </p>
    </div>
</div>
@endsection
