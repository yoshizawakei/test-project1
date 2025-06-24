<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset("css/layouts/sanitize.css") }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
    <title>COACHTECH</title>
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-logo">
                <a href="/"><img src="{{ asset("img/logo.svg") }}" alt="coachtech"></a>
            </div>
            <button class="menu-toggle" id="menu-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <div class="header-menu-container" id="header-menu-container">
                <div class="header-form">
                    <form action="{{ route("top.index") }}" method="get" class="header-form_form">
                        @csrf
                        <input name="search" type="text" placeholder="なにをお探しですか？" class="header-form_input">
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
                        <li><a href="/mypage">マイページ</a></li>
                        <li>
                            <form action="/sell" method="GET">
                                @csrf
                                <button class="header-nav_button" type="submit">出品</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById("menu-toggle");
            const headerMenuContainer = document.getElementById("header-menu-container");

            if (menuToggle && headerMenuContainer) {
                menuToggle.addEventListener("click", () => {
                    headerMenuContainer.classList.toggle("is-open");
                    menuToggle.classList.toggle("is-active");
                });
            }
        });
    </script>

    @yield('scripts')

</body>

</html>