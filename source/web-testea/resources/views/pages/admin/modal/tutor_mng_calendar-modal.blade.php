@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">教室</th>
        <td>@{{item.mdClassName}}</td>
    </tr>
    <tr>
        <th>スケジュール種別</th>
        <td>@{{item.mdTypeName}}</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>@{{item.mdDt|formatYmd}}</td>
    </tr>
    {{-- v-showは、スケジュール種別によって非表示の場合があるため --}}
    <tr v-show="item.mdStartTime">
        <th>開始時刻</th>
        <td>@{{item.mdStartTime|formatHm}}</td>
    </tr>
    <tr v-show="item.mdEndTime">
        <th>終了時刻</th>
        <td>@{{item.mdEndTime|formatHm}}</td>
    </tr>
    <tr v-show="item.mdTitle">
        <th>@{{item.mdTitle}}</th>
        <td>@{{item.mdTitleVal}}</td>
    </tr>
    <tr v-show="item.mdSubject">
        <th>教科</th>
        <td>@{{item.mdSubject}}</td>
    </tr>
    <tr v-show="item.mdFurikae">
        <th>振替情報等</th>
        <td>@{{item.mdFurikae}}</td>
    </tr>
</x-bs.table>

@overwrite