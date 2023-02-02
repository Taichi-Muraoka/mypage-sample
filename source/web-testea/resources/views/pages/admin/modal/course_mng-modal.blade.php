@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td>@{{item.apply_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>追加・変更種別</th>
        <td>@{{item.course_name}}</td>
    </tr>
    <tr>
        <th>追加・変更希望内容</th>
        <td class="nl2br">@{{item.changes_text}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.status}}</td>
    </tr>
    <tr>
        <th>事務局コメント</th>
        <td class="nl2br">@{{item.comment}}</td>
    </tr>

</x-bs.table>

@overwrite