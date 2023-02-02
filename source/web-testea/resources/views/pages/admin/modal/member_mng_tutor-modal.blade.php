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
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th>教師名</th>
        <th width="30%">授業時間</th>
        <th width="30%">回数</th>
    </x-slot>

    <tr v-for="home_teacher_std_detail in item.home_teacher_std_details" v-cloak>
        <td>@{{home_teacher_std_detail.teacher_name}}</td>
        <td>@{{home_teacher_std_detail.std_minutes}}分</td>
        <td>@{{home_teacher_std_detail.std_count}}</td>
    </tr>
</x-bs.table>

@overwrite