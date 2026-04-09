@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">
        勤怠一覧
    </h1>

    <nav class="c-attendance-nav">
        <ul class="c-attendance-nav__list">

            <li class="c-attendance-nav__item">
                <a href="?year={{ $attendanceList['prev_year'] }}&month={{ $attendanceList['prev_month'] }}"
                    class="c-attendance-nav__link">
                    <div class="c-icon c-icon--arrow"
                        style="background-image:url('/assets/back-arrow.png')"></div>

                    <span class="c-attendance-nav__link--content">前月</span>
                </a>
            </li>

            <li class="c-attendance-nav__item c-attendance-nav__item--spacing">
                <div class="c-icon c-icon--calendar"
                    style="background-image:url('/assets/calendar.png')"></div>

                <p class="c-attendance-nav__item--calendar">
                    {{ $attendanceList['current_month'] }}
                </p>
            </li>

            <li class="c-attendance-nav__item">
                <a href="?year={{ $attendanceList['next_year'] }}&month={{ $attendanceList['next_month'] }}"
                    class="c-attendance-nav__link">
                    <span class="c-attendance-nav__link--content">翌月</span>

                    <div class="c-icon c-icon--arrow"
                        style="background-image:url('/assets/forward-arrow.png')"></div>
                </a>
            </li>

        </ul>
    </nav>

    <div class="c-attendance-table__wrapper">
        <table class="c-attendance-table">

            <thead class="c-attendance-table__head">
                <tr class="c-attendance-table__head--row c-attendance-table__head--row-h45">
                    <th class="c-attendance-table__head--date">日付</th>
                    <th class="c-attendance-table__head--clock-in">出勤</th>
                    <th class="c-attendance-table__head--clock-out">退勤</th>
                    <th class="c-attendance-table__head--break">休憩</th>
                    <th class="c-attendance-table__head--total">合計</th>
                    <th class="c-attendance-table__head--detail">詳細</th>
                </tr>
            </thead>

            <tbody class="c-attendance-table__data">

                @foreach ($attendanceList['days'] as $day)
                <tr class="c-attendance-table__data--row c-attendance-table__data--row-h48">

                    <td class="c-attendance-table__data--date">
                        {{ $day['date_text'] }}
                    </td>

                    <td class="c-attendance-table__data--clock-in">
                        {{ $day['clock_in'] }}
                    </td>

                    <td class="c-attendance-table__data--clock-out">
                        {{ $day['clock_out'] }}
                    </td>

                    <td class="c-attendance-table__data--break">
                        {{ $day['break_time'] }}
                    </td>

                    <td class="c-attendance-table__data-total">
                        {{ $day['work_time'] }}
                    </td>

                    <td class="c-attendance-table__data--detail">
                        @if ($day['is_empty'])
                        <span class="c-attendance-table__data--detail-empty">
                            詳細
                        </span>
                        @else
                        <a href="{{ route('staff.attendance.detail', $day['id']) }}"
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