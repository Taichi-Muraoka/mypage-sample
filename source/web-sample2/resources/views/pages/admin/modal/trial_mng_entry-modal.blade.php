@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">申込日</th>
        <td>@{{$filters.formatYmd(item.apply_time)}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>模試名</th>
        <td>@{{item.trial_name}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.apply_state}}</td>
    </tr>

</x-bs.table>

@overwrite