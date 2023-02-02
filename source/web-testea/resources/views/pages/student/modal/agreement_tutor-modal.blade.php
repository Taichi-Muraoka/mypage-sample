@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">開始日</th>
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
        <th>家庭教師標準</th>
        <td>@{{item.dtl_std_summary}}</td>
    </tr>

</x-bs.table>

<x-bs.form-title>家庭教師標準詳細</x-bs.form-title>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th>教師名</th>
        <th>授業時間</th>
        <th>回数</th>
    </x-slot>

    <tr v-for="home_teacher_std_detail in item.home_teacher_std_details" v-cloak>
        <x-bs.td-sp caption="教師名">@{{home_teacher_std_detail.teacher_name}}</x-bs.td-sp>
        <x-bs.td-sp caption="授業時間" class="resp-column">@{{home_teacher_std_detail.std_minutes}}分</x-bs.td-sp>
        <td class="resp-clear"></td>
        <x-bs.td-sp caption="回数" class="resp-column no-border">@{{home_teacher_std_detail.std_count}}</x-bs.td-sp>
        <td class="resp-clear"></td>
    </tr>

</x-bs.table>

@overwrite