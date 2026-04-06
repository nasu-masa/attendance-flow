@extends('layouts.guest')

@section('title', 'メール認証誘導')

@section('css')
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endsection

@section('content')
<div class="p-email">

    <p class="p-email__text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <div class="p-email__button">
        <a href="http://localhost:8025"
            class="p-email__button--submit">
            認証はこちらから
        </a>
    </div>

    <div class="c-link p-email-link">
        <form action="{{ route('verification.send') }}" method="post">
            @csrf
            <button type="submit"
                class="c-link__text p-email-link__text">
                認証メールを再送する
            </button>
        </form>
    </div>

</div>

@endsection