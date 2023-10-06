@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">対応日時</th>
        <td>@{{$filters.formatYmd(item.received_date)}}&nbsp;@{{item.received_time}}</td>
    </tr>
    <tr>
        <th width="35%">登録日時</th>
        <td>@{{$filters.formatYmdHm(item.regist_time)}}</td>
    </tr>
    <tr>
        <th>担当者名</th>
        <td>@{{item.admin_name}}（@{{item.campus_name}}）</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>記録種別</th>
        <td>@{{item.kind_name}}</td>
    </tr>
    <tr>
        <th>内容</th>
        <td class="nl2br">
            @{{item.memo}}
        </td>
    </tr>

</x-bs.table>

@overwrite