@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">依頼日</th>
        <td>@{{$filters.formatYmd(item.apply_date)}}</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>希望内容</th>
        <td class="nl2br">@{{item.request}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.status_name}}</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td class="nl2br">@{{item.admin_comment}}</td>
    </tr>
</x-bs.table>

@overwrite