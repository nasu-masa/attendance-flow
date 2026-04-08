@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endsection

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">勤怠詳細</h1>

    <form
        class="c-attendance-form"
        method="POST"
        action="{{ route('staff.attendance.detail.post', [
            'id' => $attendance->id,
        ]) }}">
        @csrf

        <div class="c-attendance-table__wrapper">
            <table class="c-attendance-table">

                {{-- 名前 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--name">
                        名前
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__content--name">
                            {{ $display['user_name'] }}
                        </div>
                    </td>
                </tr>

                {{-- 日付 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--date">
                        日付
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">
                            <p class="c-attendance-table__date--year">
                                {{ $display['date_year'] }}
                            </p>
                            <p class="c-attendance-table__date--md">
                                {{ $display['date_md'] }}
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- 出勤・退勤 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--work">
                        <label for="clock_in" class="c-attendance__label">
                            出勤・退勤
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">

                            {{-- 出勤 --}}
                            @if ($display['is_pending'])
                            <p class="c-attendance__text">
                                {{ $display['clock_in'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                id="clock_in"
                                name="clock_in"
                                class="c-attendance__input"
                                value="{{ $display['clock_in'] }}">
                            @endif

                            <span class="c-attendance-table__separator">~</span>

                            {{-- 退勤 --}}
                            @if ($display['is_pending'])
                            <p class="c-attendance__text">
                                {{ $display['clock_out'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                id="clock_out"
                                name="clock_out"
                                class="c-attendance__input"
                                value="{{ $display['clock_out'] }}">
                            @endif

                        </div>
                    </td>
                </tr>

                {{-- 休憩1 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--break">
                        <label for="break_start_1" class="c-attendance__label">
                            休憩
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">

                            {{-- 開始 --}}
                            @if ($display['is_pending'])

                            <p class="c-attendance__text">
                                {{ $display['break_stat_1'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                id="break_start_1"
                                name="break_start_1"
                                class="c-attendance__input"
                                value="{{ $display['break_start_1'] }}">
                            @endif

                            <span class="c-attendance-table__separator">~</span>

                            {{-- 終了 --}}
                            @if ($display['is_pending'])

                            <p class="c-attendance__text">
                                {{ $display['break_stat_2'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                id="break_end_1"
                                name="break_end_1"
                                class="c-attendance__input"
                                value="{{ $display['break_end_1'] }}">
                            @endif

                        </div>
                    </td>
                </tr>

                {{-- 休憩2 --}}
                @if (!$display['is_pending'] || !empty($display['break_start_2']))
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--break">
                        <label for="break_start_2" class="c-attendance__label">
                            休憩２
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">

                            {{-- 開始 --}}
                            @if ($display['is_pending'])
                            <p class="c-attendance__text">
                                {{ $display['break_start_2'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                id="break_start_2"
                                name="break_start_2"
                                class="c-attendance__input"
                                value="{{ $display['break_start_2'] }}">
                            @endif

                            <span class="c-attendance-table__separator">~</span>

                            {{-- 終了 --}}
                            @if ($display['is_pending'])
                            <p class="c-attendance__text">
                                {{ $display['break_end_2'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                id="break_end_2"
                                name="break_end_2"
                                class="c-attendance__input"
                                value="{{ $display['break_end_2'] }}">
                            @endif

                        </div>
                    </td>
                </tr>
                @endif

                {{-- 備考 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--remark">
                        <label for="remarks" class="c-attendance__label">
                            備考
                        </label>
                    </th>
                    <td class="c-attendance-table__content">

                        @if ($display['is_pending'])
                        <p class="c-attendance__remark">
                            {{ $display['remarks'] }}
                        </p>
                        @else
                        <textarea
                            id="remarks"
                            name="remarks"
                            class="c-attendance__textarea">{{ $display['remarks'] }}</textarea>
                        @endif
                    </td>
                </tr>

            </table>
        </div>

        {{-- ボタン --}}
        @if ($display['is_pending'])
        <p class="c-attendance__disabled">
            *承認待ちのため修正はできません。
        </p>
        @else
        <div class="c-attendance-table__button-wrapper">
            <button type="submit"
                class="c-attendance-table__button
                    c-attendance-table__button--submit">
                修正
            </button>
        </div>
        @endif

    </form>

    @if ($errors->any())
    <div class="c-error--lg">
        <ul class="c-error__list">
            @foreach ($errors->all() as $error)
            <li class="c-error__text c-error__text--sm">
                {{ $error }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>

@endsection