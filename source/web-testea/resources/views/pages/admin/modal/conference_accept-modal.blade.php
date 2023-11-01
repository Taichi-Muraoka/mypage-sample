@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>校舎</th>
        <td>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>ブース</th>
        <td>@{{item.booth_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>第１希望日時</th>
        <td>@{{$filters.formatYmd(item.conference_date1)}} @{{$filters.formatHm(item.start_time1)}}</td>
    </tr>
    <tr>
        <th>第２希望日時</th>
        <td>@{{$filters.formatYmd(item.conference_date2)}} @{{$filters.formatHm(item.start_time2)}}</td>
    </tr>
    <tr>
        <th>第３希望日時</th>
        <td>@{{$filters.formatYmd(item.conference_date3)}} @{{$filters.formatHm(item.start_time3)}}</td>
    </tr>
    <tr>
        <th>特記事項</th>
        <td>@{{item.comment}}</td>
    </tr>
    <tr>
        <th>面談日</th>
        <td>@{{$filters.formatYmd(item.conference_date)}}</td>
    </tr>
    <tr>
        <th>面談担当者</th>
        <td>@{{item.adm_name}}</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>@{{item.start_time}}</td>
    </tr>
    <tr>
        <th>管理者メモ</th>
        <td class="nl2br">@{{item.memo}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.status}}</td>
    </tr>

</x-bs.table>

@overwrite