@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title c-attendance__title--spacing">
        スタッフ一覧
    </h1>

    <div class="c-attendance-table__wrapper">
        <table class="c-attendance-table c-attendance-table-staff">

            <thead class="c-attendance-table__head">
                <tr class="c-attendance-table__head--row c-attendance-table__head--row-h42">
                    <th class="c-attendance-table__head--staff-name">名前</th>
                    <th class="c-attendance-table__head--email">メールアドレス</th>
                    <th class="c-attendance-table__head--management">月次管理</th>
                </tr>
            </thead>

            <tbody class="c-attendance-table__data">
                @foreach ($users as $user)
                <tr class="c-attendance-table__data--row c-attendance-table__data--row-h43">

                    <td class="c-attendance-table__data--staff-name">
                        {{ $user->name }}
                    </td>

                    <td class="c-attendance-table__data--email">
                        {{ $user->email }}
                    </td>

                    <td class="c-attendance-table__data--management">
                        <a href="{{ route('admin.attendance.staff', $user->id) }}"
                            class="c-attendance-table__data--detail-link">
                            詳細
                        </a>
                    </td>

                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

@endsection