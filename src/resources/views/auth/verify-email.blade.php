@extends('layouts.auth')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
    <div class="container">
        @if (session('status') == 'verification-link-sent')
            <div class="status-message">
                {{ __('新しい認証リンクが、登録時に入力されたメールアドレスに送信されました。') }}
            </div>
        @endif

        <p class="message">
            {{ __('登録していただいたメールアドレスに認証メールを送付しました。') }}<br>
            {{ __('メール認証を完了してください。') }}
        </p>

        <a href="#" class="main-button">
            {{ __('認証はこちらから') }}
        </a>
        <p class="text-sm text-gray-500 mb-4">
            {{ __('上記のボタンは、メール内の認証リンクをクリックすることを促しています。') }}<br>
            {{ __('メールをご確認ください。') }}
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="inline-block mt-4">
            @csrf
            <button type="submit" class="resend-link">
                {{ __('認証メールを再送する') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="inline-block ml-4">
            @csrf
            <button type="submit" class="resend-link">
                {{ __('ログアウト') }}
            </button>
        </form>
    </div>
@endsection