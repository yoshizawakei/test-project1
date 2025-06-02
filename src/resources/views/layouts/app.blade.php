<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset("css/sanitize.css") }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
    <title>COACHTECH</title>
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-logo">
                <a href="/"><img src="{{ asset("img/logo.svg") }}" alt="coachtech"></a>
            </div>
            <div class="header-form">
                <form action="#" class="header-form_form">
                    <input name="search" type="text" placeholder="なにをお探しですか？" class="header-form_input" name="#">
                    <button class="header-form_button">検索</button>
                </form>
            </div>
            <nav class="header-nav">
                <ul class="header-nav_list">
                    @if(Auth::check())
                        <li>
                            <form action="/logout" method="POST">
                                @csrf
                                <button class="header-nav_logout-button" type="submit">ログアウト</button>
                            </form>
                        </li>
                    @else
                        <li><a href="/login">ログイン</a></li>
                    @endif
                    <li><a href="/profile/mypage">マイページ</a></li>
                    <li>
                        <form action="#">
                            @csrf
                            <button class="header-nav_button" type="submit">出品</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
        @yield('scripts')
        
    </main>

</body>

</html>