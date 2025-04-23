@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">模試名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>学年</th>
        <td>@{{item.cls}}</td>
    </tr>
    <tr>
        <th>開催日</th>
        <td>@{{$filters.formatYmd(item.trial_date)}}</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>@{{$filters.formatHm(item.start_time)}}</td>

    </tr>
    <tr>
        <th>終了時刻</th>
        <td>@{{$filters.formatHm(item.end_time)}}</td>
    </tr>

</x-bs.table>

@overwrite