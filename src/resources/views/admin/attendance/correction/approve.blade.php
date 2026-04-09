@extends('layouts.admin')

@section('title', '修正申請承認')

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">勤怠詳細</h1>

    <form
        class="c-attendance-form"
        method="POST"
        action="{{ route('admin.attendance.correction.approve', [
            'attendance_correct_request_id' => $correctionRequestDetail['id']
        ]) }}">
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
                            <p class="c-attendance-table__date--year">
                                {{ $correctionRequestDetail['date_year'] }}
                            </p>
                            <p class="c-attendance-table__date--md">
                                {{ $correctionRequestDetail['date_md'] }}
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- 出勤・退勤 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--work">
                        <label class="c-attendance__label">
                            出勤・退勤
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">
                            <p class="c-attendance__text">
                                {{ $correctionRequestDetail['clock_in'] }}
                            </p>

                            <span class="c-attendance-table__separator">~</span>

                            <p class="c-attendance__text">
                                {{ $correctionRequestDetail['clock_out'] }}
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- 休憩1 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--break">
                        <label class="c-attendance__label">
                            休憩
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">
                            <p class="c-attendance__text">
                                {{ $correctionRequestDetail['break_start_1'] }}
                            </p>

                            <span class="c-attendance-table__separator">~</span>

                            <p class="c-attendance__text">
                                {{ $correctionRequestDetail['break_end_1'] }}
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- 休憩2 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--break">
                        <label class="c-attendance__label">
                            休憩２
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <div class="c-attendance-table__range">
                            <p class="c-attendance__text">
                                {{ $correctionRequestDetail['break_start_2'] }}
                            </p>

                            <span class="c-attendance-table__separator">~</span>

                            <p class="c-attendance__text">
                                {{ $correctionRequestDetail['break_end_2'] }}
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- 備考 --}}
                <tr class="c-attendance-table__row">
                    <th class="c-attendance-table__title c-attendance-table__title--remark">
                        <label class="c-attendance__label">
                            備考
                        </label>
                    </th>
                    <td class="c-attendance-table__content">
                        <p class="c-attendance__remark">
                            {{ $correctionRequestDetail['remarks'] }}
                        </p>
                    </td>
                </tr>

            </table>
        </div>

        {{-- ボタン --}}
        @if ($correctionRequestDetail['is_pending'])
        <div class="c-attendance-table__button-wrapper
                    c-attendance-table__button-wrapper--spacing">
            <button
                type="submit"
                class="c-attendance-table__button
                    c-attendance-table__button--submit">
                承認
            </button>
        </div>
        @else
        <div class="c-attendance-table__button-wrapper
                    c-attendance-table__button-wrapper--spacing">
            <p class="c-attendance-table__button
                    c-attendance-table__button--approve">
                承認済み
            </p>
        </div>
        @endif

    </form>
</div>

@endsection