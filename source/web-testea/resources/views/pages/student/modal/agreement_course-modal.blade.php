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
<x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th>教師名</th>
        <th>日付</th>
        <th>開始時刻</th>
        <th>授業時間</th>
        <th>教科</th>
    </x-slot>

    <tr v-for="extra_ind_detail in item.extra_ind_details" v-cloak>
        <x-bs.td-sp caption="教師名">@{{extra_ind_detail.teacher_name}}</x-bs.td-sp>
        <x-bs.td-sp caption="日付" class="resp-column wide">@{{extra_ind_detail.extra_date|formatYmd}}</x-bs.td-sp>
        <td class="resp-clear"></td>
        <x-bs.td-sp caption="開始時刻" class="resp-column no-border">@{{extra_ind_detail.start_time|formatHm}}</x-bs.td-sp>
        <x-bs.td-sp caption="授業時間" class="resp-column no-border">@{{extra_ind_detail.r_minutes}}分</x-bs.td-sp>
        <x-bs.td-sp caption="教科" class="resp-column no-border">@{{extra_ind_detail.curriculum_name}}</x-bs.td-sp>
        <td class="resp-clear"></td>
    </tr>

</x-bs.table>

@overwrite
