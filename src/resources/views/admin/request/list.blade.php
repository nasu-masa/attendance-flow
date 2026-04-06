@extends('layouts.admin')

@section('title', '申請一覧')

@section('content')

<div class="c-attendance__card">
    <h1 class="c-attendance__title">申請一覧</h1>

    <div class="c-tabs">
        {{-- タブ --}}
        <a href="?tab=pending"
            class="c-tabs__status {{ $display['isPending'] ? 'active' : '' }}">
            承認待ち
        </a>

        <a href="?tab=approved"
            class="c-tabs__status {{ $display['isApproved'] ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <hr class="c-under-line">

    <div class="c-attendance-table__wrapper">
        <table class="c-attendance-table">

            {{-- ヘッダー --}}
            <thead class="c-attendance-table__head">
                <tr class="c-attendance-table__head--row c-attendance-table__head--row-h42">
                    <th class="c-attendance-table__head--status">状態</th>
                    <th class="c-attendance-table__head--name">名前</th>
                    <th class="c-attendance-table__head--target-date">対象日時</th>
                    <th class="c-attendance-table__head--remark">申請理由</th>
                    <th class="c-attendance-table__head--request-date">申請日時</th>
                    <th class="c-attendance-table__head--detail">詳細</th>
                </tr>
            </thead>

            {{-- データ --}}
            <tbody class="c-attendance-table__data">
                @foreach ($display['requests'] as $req)
                <tr class="c-attendance-table__data--row c-attendance-table__data--row-h43">

                    <td class="c-attendance-table__data--status">
                        {{ $req['status_label'] }}
                    </td>

                    <td class="c-attendance-table__data--name"
                        title="{{ $req['user_name_full'] }}">
                        {{ $req['user_name']  }}
                    </td>

                    <td class="c-attendance-table__data--target-date">
                        {{ $req['target_date'] }}
                    </td>

                    <td class="c-attendance-table__data--remark"
                        title="{{ $req['remarks_full'] }}">
                        {{ $req['remarks'] }}
                    </td>

                    <td class="c-attendance-table__data--request-date">
                        {{ $req['request_date'] }}
                    </td>

                    <td class="c-attendance-table__data--detail">
                        <a href="{{ route('admin.request.approve.show', $req['request_id']) }}"
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