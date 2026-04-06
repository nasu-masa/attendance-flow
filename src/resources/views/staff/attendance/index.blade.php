@extends('layouts.app')

@section('title', '今月の出勤一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endsection

@section('nav')

@if ($display->isFinished())

@include('partials.finished-nav')

@else

@include('partials.default-nav')

@endif

@endsection

@section('content')

<div class="c-attendance__card p-attendance__card">

    {{-- ステータス --}}
    <div class="p-attendance__status">
        <p class="p-attendance__status--inner">
            {{ $statusLabels->statusText() }}
        </p>
    </div>

    {{-- 日付と時刻 --}}
    <div class="p-attendance__datetime">
        <p class="p-attendance__date">
            {{ $today->isoFormat('YYYY年M月D日(ddd)') }}
        </p>
        <h1 class="p-attendance__time">{{ $time }}</h1>
    </div>

    {{-- ボタン --}}
    <div class="p-attendance__actions">

        {{-- 出勤前 --}}
        @if ($display->isOut())

        <form method="POST" action="{{ route('staff.attendance.action') }}">
            @csrf
            <input type="hidden" name="action" value="{{ $display->startAction() }}">

            <button type="submit"
                class="p-attendance__button p-attendance__button--start">
                出勤
            </button>
        </form>

        {{-- 勤務中 --}}
        @elseif ($display->isWorking())

        <form method="POST" action="{{ route('staff.attendance.action') }}">
            @csrf
            <input type="hidden" name="action" value="{{ $display->finishAction() }}">

            <button type="submit"
                class="p-attendance__button p-attendance__button--finish">
                <ion-icon name="home" style="color: white;"></ion-icon>
                退勤
            </button>
        </form>

        <form method="POST" action="{{ route('staff.attendance.action') }}">
            @csrf
            <input type="hidden" name="action" value="{{ $display->breakInAction() }}">

            <button type="submit"
                class="p-attendance__button p-attendance__button--break-in">
                <ion-icon name="cafe-outline" class="p-break-icon"></ion-icon>
                休憩入
            </button>
        </form>

        {{-- 休憩中 --}}
        @elseif ($display->isBreak())

        <form method="POST" action="{{ route('staff.attendance.action') }}">
            @csrf
            <input type="hidden" name="action" value="{{ $display->breakOutAction() }}">

            <button type="submit"
                class="p-attendance__button p-attendance__button--break-out">
                <ion-icon name="refresh-outline"></ion-icon>
                休憩戻
            </button>
        </form>

        {{-- 退勤後 --}}
        @elseif ($display->isFinished())

        <p class="p-attendance__message">
            お疲れ様でした。
        </p>

        @else

        <form method="POST" action="{{ route('staff.attendance.action') }}">
            @csrf
            <input type="hidden" name="action" value="{{ $display->startAction() }}">

            <button type="submit"
                class="p-attendance__button p-attendance__button--start">
                出勤
            </button>
        </form>

        @endif

    </div>

</div>

@endsection