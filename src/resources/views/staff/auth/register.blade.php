@extends('layouts.guest')

@section('title', '会員登録')

@section('css')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">

@endsection

@section('content')

<div class="c-card">
    <h1 class="c-card__title p-register__title">会員登録</h1>

    <form action="{{ route('register.post') }}" method="POST">
        @csrf

        {{-- 名前 --}}
        <div class="c-input">
            <label class="c-input__label">名前</label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="c-input__field c-input--sm">

            <div class="c-error c-error--sm">
                <span class="c-error__text">
                    @error('name')
                    {{ $message }}
                    @enderror
                </span>
            </div>
        </div>

        {{-- メールアドレス --}}
        <div class="c-input">
            <label class="c-input__label">メールアドレス</label>
            <input
                type="text"
                name="email"
                value="{{ old('email') }}"
                class="c-input__field c-input--sm">

            <div class="c-error c-error--lg">
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
                class="c-input__field c-input--sm">

            <div class="c-error c-error--md">
                <span class="c-error__text">
                    @error('password')
                    {{ $message }}
                    @enderror
                </span>
            </div>
        </div>

        {{-- 確認用パスワード --}}
        <div class="c-input">
            <label class="c-input__label">確認用パスワード</label>
            <input
                type="password"
                name="password_confirmation"
                class="c-input__field c-input--sm">

            <div class="c-error c-error--xl">
                <span class="c-error__text">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </span>
            </div>
        </div>

        <div class="l-button-wrapper c-button-wrapper">
            <button
                type="submit"
                class="c-button c-button--lg c-button--primary">
                登録する
            </button>
        </div>
    </form>

    <div class="c-link p-login__link">
        <a href="/login" class="c-link__text">ログインはこちら</a>
    </div>
</div>

@endsection