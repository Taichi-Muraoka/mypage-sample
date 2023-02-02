@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">申込日</th>
        <td>@{{item.apply_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>イベント名</th>
        <td>@{{item.event_name}}</td>
    </tr>
    <tr>
        <th>参加人数</th>
        <td>@{{item.members}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.changes_state}}</td>
    </tr>
</x-bs.table>

@overwrite