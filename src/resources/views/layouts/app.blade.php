<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

    @yield('css')
</head>

<body class="l-body">

    <header class="l-header">
        <div class="l-header__container">
            <a href="{{ route('staff.attendance.index') }}" class="l-header__logo-link">
                <div class="l-header__inner">
                    <img src="{{ asset('assets/COACHTECHヘッダーロゴ.png') }}"
                        alt="COACHTECH"
                        class="l-header__logo">
                </div>
            </a>

            <nav class="c-nav">

                <ul class="c-nav__list">
                    @yield('nav', view('partials.default-nav'))

                    <li class="c-nav__item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="c-nav__link c-nav__link--button">
                                ログアウト
                            </button>
                        </form>
                    </li>

                </ul>

            </nav>

    </header>

    @if (session('greeting'))
    <div class="c-flash c-flash--success">
        <span class="c-flash__inner">
            {{ session('greeting') }}
        </span>
    </div>
    @endif

    @if (session('success'))
    <div class="c-flash">
        <span class="c-flash__inner c-flash--success">
            {{ session('success') }}
        </span>
    </div>
    @endif

    <main class="l-main">

        @yield('content')

    </main>

    @yield('scripts')

    <script src="{{ asset('js/flash.js') }}"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>

</html>