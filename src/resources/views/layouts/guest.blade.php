<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title')</title>

        <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

        @yield('css')
    </head>

    <body>

        <header class="l-header">
            <div class="l-header__container">
                <div class="l-header__content">
                    <img src="{{ asset('assets/COACHTECHヘッダーロゴ.png') }}"
                        alt="COACHTECH"
                        class="l-header__logo">
                </div>
            </div>
        </header>

        <main class="l-main">

            @yield('content')

        </main>

    </body>

</html>