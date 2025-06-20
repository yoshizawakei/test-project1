@extends('layouts.auth')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register-container">
        <h2 class="register-title">会員登録</h2>
        <form method="post" action="/register" class="register-form" novalidate>
            @csrf
            <div class="form-group">
                <label for="name" class="form-label">ユーザー名</label>
                <input id="name" type="text" class="form-control" name="name"
                    value="{{ old('name') }}" autocomplete="name" autofocus>
                @error('name')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">メールアドレス</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" autocomplete="email">
                @error('email')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">パスワード</label>
                <input id="password" type="password" class="form-control" name="password" autocomplete="new-password">
                @error('password')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password-confirm" class="form-label">確認用パスワード</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                @error('password_confirmation')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                登録する
            </button>

            @if (Route::has('login'))
                <a class="login-link" href="/login">
                    ログインはこちら
                </a>
            @endif
        </form>
    </div>
@endsection