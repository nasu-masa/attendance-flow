@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">勤怠詳細</h1>

    <form
        class="c-attendance-form"
        method="post"
        action="{{ route('admin.attendance.correction', ['id' => $attendance->id]) }}">
        @csrf
        @method('PATCH')

        <div class="c-attendance-table__wrapper">
            <table class="c-attendance-table">

                {{-- 名前 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--name">
                        名前
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__content--name">
                            {{ $attendance->user->name }}
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
                            <p class="c-attendance-table__date--year">{{ $attendanceDetail['date_year'] }}</p>
                            <p class="c-attendance-table__date--md">{{ $attendanceDetail['date_md'] }}</p>
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

                            <input type="text" id="clock_in" name="clock_in"
                                class="c-attendance__input"
                                value="{{ $attendanceDetail['clock_in'] }}">

                            <span class="c-attendance-table__separator">~</span>

                            <input type="text" id="clock_out" name="clock_out"
                                class="c-attendance__input"
                                value="{{ $attendanceDetail['clock_out'] }}">

                        </div>
                    </td>
                </tr>

                {{-- 休憩 --}}
                @foreach ($attendanceDetail['breaks'] as $break)
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--break">
                        <label class="c-attendance__label">
                            @if ($loop->first)
                                休憩
                            @else
                                休憩{{ $loop->iteration }}
                            @endif
                        </label>
                    </th>

                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">

                            {{-- 開始 --}}
                            @if ($attendanceDetail['is_pending'])
                            <p class="c-attendance__text">
                                {{ $break['start'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                name="breaks[{{ $loop->index }}][start]"
                                class="c-attendance__input"
                                value="{{ $break['start'] }}">
                            @endif

                            <span class="c-attendance-table__separator">~</span>

                            {{-- 終了 --}}
                            @if ($attendanceDetail['is_pending'])
                            <p class="c-attendance__text">
                                {{ $break['end'] }}
                            </p>
                            @else
                            <input
                                type="text"
                                name="breaks[{{ $loop->index }}][end]"
                                class="c-attendance__input"
                                value="{{ $break['end'] }}">
                            @endif

                        </div>
                    </td>
                </tr>
                @endforeach
                {{-- 備考 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--remark">
                        <label for="remarks" class="c-attendance__label">
                            備考
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <textarea id="remarks" name="remarks"
                            class="c-attendance__textarea">{{ $attendanceDetail['remarks'] }}</textarea>
                    </td>
                </tr>

            </table>
        </div>

        <div class="c-attendance-table__button-wrapper">
            <button type="submit"
                class="c-attendance-table__button
                    c-attendance-table__button--submit">
                修正
            </button>
        </div>

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