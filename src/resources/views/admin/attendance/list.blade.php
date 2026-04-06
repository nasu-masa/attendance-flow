@extends('layouts.admin')

@section('title', 'スタッフ勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/pages/attendance.css') }}">
@endsection

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">
        {{ $display['current_date'] }} の勤怠
    </h1>

    <nav class="c-attendance-nav">
        <ul class="c-attendance-nav__list">

            {{-- 前日 --}}
            <li class="c-attendance-nav__item">
                <a href="?date={{ $display['prev_date'] }}" class="c-attendance-nav__link">
                    <div class="c-icon c-icon--arrow"
                        style="background-image:url('/assets/back-arrow.png')"></div>
                    <span class="c-attendance-nav__link--content">前日</span>
                </a>
            </li>

            {{-- カレンダー --}}
            <li class="c-attendance-nav__item c-attendance-nav__item--spacing">
                <div class="c-icon c-icon--calendar js-calendar-toggle"
                    style="background-image:url('/assets/calendar.png')">
                </div>
                <p class="c-attendance-nav__item--calendar">
                    {{ $display['current_date_slash'] }}
                </p>
            </li>

            {{-- 翌日 --}}
            <li class="c-attendance-nav__item">
                <a href="?date={{ $display['next_date'] }}" class="c-attendance-nav__link">
                    <span class="c-attendance-nav__link--content">翌日</span>
                    <div class="c-icon c-icon--arrow"
                        style="background-image:url('/assets/forward-arrow.png')"></div>
                </a>
            </li>

        </ul>
    </nav>

    <div class="c-attendance-table__wrapper">
        <table class="c-attendance-table">

            {{-- ヘッダー --}}
            <thead class="c-attendance-table__head">
                <tr class="c-attendance-table__head--row c-attendance-table__head--row-h43">
                    <th class="c-attendance-table__head--user">名前</th>
                    <th class="c-attendance-table__head--clock-in">出勤</th>
                    <th class="c-attendance-table__head--clock-out">退勤</th>
                    <th class="c-attendance-table__head--break">休憩</th>
                    <th class="c-attendance-table__head--total">合計</th>
                    <th class="c-attendance-table__head--detail">詳細</th>
                </tr>
            </thead>

            {{-- データ --}}
            <tbody class="c-attendance-table__data">
                @foreach ($display['attendances'] as $attendance)
                <tr class="c-attendance-table__data--row c-attendance-table__data--row-h45">

                    <td class="c-attendance-table__data--user">
                        {{ $attendance['name'] }}
                    </td>

                    <td class="c-attendance-table__data--clock-in">
                        {{ $attendance['clock_in'] }}
                    </td>

                    <td class="c-attendance-table__data--clock-out">
                        {{ $attendance['clock_out'] }}
                    </td>

                    <td class="c-attendance-table__data--break">
                        {{ $attendance['break'] }}
                    </td>

                    <td class="c-attendance-table__data-total">
                        {{ $attendance['total'] }}
                    </td>

                    <td class="c-attendance-table__data--detail">
                        @if (empty($attendance['id']))
                        <span class="c-attendance-table__data--detail-empty">
                            詳細
                        </span>
                        @else
                        <a href="{{ route('admin.attendance.detail', $attendance['id']) }}"
                            class="c-attendance-table__data--detail-link">
                            詳細
                        </a>
                        @endif
                    </td>

                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>

@endsection