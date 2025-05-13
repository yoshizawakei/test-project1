<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset("css/sanitize.css") }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
    <title>COACHTECH</title>
</head>
<body>
<header class="header">
    <div class="header-inner">
        <div class="header-logo">
            <a href="#"></a>
        </div>
        <div class="header-form">
            <form action="#" class="header-form_form">
                <input type="text" placeholder="なにをお探しですか？" class="header-form_input" name="#">
                <button class="header-form_button">検索</button>
            </form>
        </div>
        <nav class="header-nav">
            <ul class="header-nav_list">
                <li><a href="#">ログイン</a></li>
                <li><a href="#">マイページ</a></li>
                <li>
                    <form action="#">
                        @csrf
                        <button>出品</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</header>
<main>
    @yield('content')
</main>
    
</body>
</html>