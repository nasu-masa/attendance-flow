@extends('layouts.guest')

@section('title', 'ログイン')

@section('css')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">

@endsection

@section('content')

<div class="c-card">
    <h2 class="c-card__title p-login__title">ログイン</h2>

    <form action="{{ route('staff.login.post') }}" method="post">
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
                ログインする
            </button>
        </div>
    </form>

    <div class="c-link p-register__link">
        <a href="/register" class="c-link__text">会員登録はこちら</a>
    </div>
</div>
@endsection