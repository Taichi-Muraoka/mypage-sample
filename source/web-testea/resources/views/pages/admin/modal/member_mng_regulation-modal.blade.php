@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">教室</th>
        <td>@{{item.dtl_room_name}}</td>
    </tr>
    <tr>
        <th>開始日</th>
        <td>@{{item.dtl_startdate|formatYmd}}</td>
    </tr>
    <tr>
        <th>終了日</th>
        <td>@{{item.dtl_enddate|formatYmd}}</td>
    </tr>
    <tr>
        <th>月額</th>
        <td>@{{item.dtl_tuition|toLocaleString}}</td>
    </tr>
    <tr>
        <th>規定情報</th>
        <td>@{{item.dtl_regular_summary}}</td>
    </tr>

</x-bs.table>

<x-bs.form-title>規定詳細</x-bs.form-title>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th>教師名</th>
        <th width="10%">曜日</th>
        <th width="15%">開始時刻</th>
        <th width="15%">授業時間</th>
        <th width="10%">回数</th>
        <th width="10%">教科</th>
    </x-slot>

    <tr v-for="regular_detail in item.regular_details" v-cloak>
        <td>@{{regular_detail.teacher_name}}</td>
        <td>@{{regular_detail.weekday}}</td>
        <td>@{{regular_detail.start_time|formatHm}}</td>
        <td>@{{regular_detail.r_minutes}}分</td>
        <td>@{{regular_detail.r_count}}</td>
        <td>@{{regular_detail.curriculum_name}}</td>
    </tr>
</x-bs.table>

@overwrite