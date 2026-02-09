@extends('layouts.app')

@section('title', 'Đăng Ký - System Sight')

@section('body-class', 'auth-page')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Đăng Ký</h1>
        <p class="auth-subtitle">
            Tạo tài khoản System Sight
        </p>

        @if ($errors->any())
            <div class="alert error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input type="text" name="username" placeholder="Tên đăng nhập" required value="{{ old('username') }}">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit" class="btn auth-btn">Đăng Ký</button>
        </form>

        <p class="auth-link">
            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
