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
        <div class="main-button_div">
            <a href="https://mailtrap.io/inboxes/3734605/messages" class="main-button">
                {{ __('認証はこちらから') }}
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="inline-block">
            @csrf
            <button type="submit" class="resend-link">
                {{ __('認証メールを再送する') }}
            </button>
        </form>
    </div>
@endsection