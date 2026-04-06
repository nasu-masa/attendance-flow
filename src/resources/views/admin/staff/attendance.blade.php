@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧')

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">
        {{ $user->name }}さんの勤怠
    </h1>

    <nav class="c-attendance-nav">
        <ul class="c-attendance-nav__list">

            {{-- 前月 --}}
            <li class="c-attendance-nav__item">
                <a href="?year={{ $display['prev_year'] }}&month={{ $display['prev_month'] }}"
                    class="c-attendance-nav__link">
                    <div class="c-icon c-icon--arrow"
                        style="background-image:url('/assets/back-arrow.png')"></div>
                    <span class="c-attendance-nav__link--content">前月</span>
                </a>
            </li>

            {{-- カレンダー --}}
            <li class="c-attendance-nav__item c-attendance-nav__item--spacing">
                <div class="c-icon c-icon--calendar"
                    style="background-image:url('/assets/calendar.png')"></div>

                <p class="c-attendance-nav__item--calendar">
                    {{ $display['current_month'] }}
                </p>
            </li>

            {{-- 翌月 --}}
            <li class="c-attendance-nav__item">
                <a href="?year={{ $display['next_year'] }}&month={{ $display['next_month'] }}"
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

            {{-- ヘッダー --}}
            <thead class="c-attendance-table__head">
                <tr class="c-attendance-table__head--row c-attendance-table__head--row-h43">
                    <th class="c-attendance-table__head--date">日付</th>
                    <th class="c-attendance-table__head--clock-in">出勤</th>
                    <th class="c-attendance-table__head--clock-out">退勤</th>
                    <th class="c-attendance-table__head--break">休憩</th>
                    <th class="c-attendance-table__head--total">合計</th>
                    <th class="c-attendance-table__head--detail">詳細</th>
                </tr>
            </thead>

            {{-- データ --}}
            <tbody class="c-attendance-table__data">
                @foreach ($display['days'] as $day)
                <tr class="c-attendance-table__data--row c-attendance-table__data--row-h45">

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
                        <a href="{{ route('admin.attendance.detail', $day['id']) }}"
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

    {{-- CSV 出力 --}}
    <div class="c-attendance-table__button-wrapper">
        <a href="{{ $csvUrl }}"
            class="c-attendance-table__button c-attendance-table__button--export">
            <ion-icon name="document-text-outline"
                class="c-icon--text"></ion-icon>
            CSV出力
        </a>
    </div>

</div>

@endsection