@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="70%">体験授業生徒</th>
        <th>体験授業日</th>
        <th>会員ステータス</th>
        <th>入会日</th>
    </tr>
    <tr v-for="item in item.schedules" v-cloak>
        <td>@{{item.student_name}}</td>
        <td>@{{$filters.formatYmdDay(item.target_date)}}</td>
        <td>@{{item.stu_status}}</td>
        <td>@{{$filters.formatYmd(item.enter_date)}}</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite