<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/login.css') }}">
</head>

<body class="login-body">

    <header class="login-header">
        <div class="login-header__container">
            <h1 class="login-header__content">
                <img
                    src="{{ asset('assets/COACHTECHヘッダーロゴ.png') }}"
                    alt="COACHTECH"
                    class="login-header__logo">
            </h1>
        </div>
    </header>

    <main class="login-main">
        <div class="c-card">
            <h2 class="c-card__title login-title">管理者ログイン</h2>

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf

                {{-- メールアドレス --}}
                <div class="c-input">
                    <label class="c-input__label">メールアドレス</label>
                    <input
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        class="c-input__field c-input--md">

                    <div class="c-error c-error--xl">
                        <span class="c-error__text">
                            @error('email')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                {{-- パスワード --}}
                <div class="c-input">
                    <label class="c-input__label">パスワード</label>
                    <input
                        type="password"
                        name="password"
                        class="c-input__field c-input--md">

                    <div class="c-error c-error--xl">
                        <span class="c-error__text">
                            @error('password')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="l-button-wrapper c-button-wrapper">
                    <button
                        type="submit"
                        class="c-button c-button--lg c-button--primary">
                        管理者ログインする
                    </button>
                </div>
            </form>

        </div>
    </main>

</body>

</html>