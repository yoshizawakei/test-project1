@extends('layouts.auth')

@section("css")
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section("content")
    <div class="login-container">
        <h2 class="login-title">ログイン</h2>
        <form method="post" action="/login" class="login-form">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">メールアドレス</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                @error('email')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">パスワード</label>
                <input id="password" type="password" class="form-control" name="password" autocomplete="current-password">
                @error('password')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                ログインする
            </button>

            @if (Route::has('register'))
                <a class="register-link" href="/register">
                    会員登録はこちら
                </a>
            @endif
        </form>
    </div>
@endsection