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
            <a href="/"><img src="{{ asset("img/logo.svg") }}" alt="coachtech"></a>
        </div>
    </div>
</header>
<main>
    @yield('content')
</main>
    
</body>
</html>