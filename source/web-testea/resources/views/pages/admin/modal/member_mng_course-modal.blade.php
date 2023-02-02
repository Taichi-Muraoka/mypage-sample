@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">教室</th>
        <td>@{{item.dtl_room_name}}</td>
    </tr>
    <tr>
        <th>講習料</th>
        <td>@{{item.dtl_price|toLocaleString}}</td>
    </tr>
    <tr>
        <th>講習名</th>
        <td>@{{item.dtl_name}}</td>
    </tr>

</x-bs.table>

<x-bs.form-title>短期個別講習詳細</x-bs.form-title>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th>教師名</th>
        <th width="15%">日付</th>
        <th width="15%">開始時刻</th>
        <th width="15%">授業時間</th>
        <th width="10%">教科</th>
    </x-slot>

    <tr v-for="extra_ind_detail in item.extra_ind_details" v-cloak>
        <td>@{{extra_ind_detail.teacher_name}}</td>
        <td>@{{extra_ind_detail.extra_date|formatYmd}}</td>
        <td>@{{extra_ind_detail.start_time|formatHm}}</td>
        <td>@{{extra_ind_detail.r_minutes}}分</td>
        <td>@{{extra_ind_detail.curriculum_name}}</td>
    </tr>
</x-bs.table>

@overwrite