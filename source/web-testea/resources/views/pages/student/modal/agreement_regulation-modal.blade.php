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
<x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th>教師名</th>
        <th>曜日</th>
        <th>開始時刻</th>
        <th>授業時間</th>
        <th>回数</th>
        <th>教科</th>
    </x-slot>

    <tr v-for="regular_detail in item.regular_details" v-cloak>
        <x-bs.td-sp caption="教師名">@{{regular_detail.teacher_name}}</x-bs.td-sp>
        <x-bs.td-sp caption="曜日" class="resp-column">@{{regular_detail.weekday}}</x-bs.td-sp>
        <x-bs.td-sp caption="開始時刻" class="resp-column">@{{regular_detail.start_time|formatHm}}</x-bs.td-sp>
        <x-bs.td-sp caption="授業時間" class="resp-column">@{{regular_detail.r_minutes}}分</x-bs.td-sp>
        <td class="resp-clear"></td>
        <x-bs.td-sp caption="回数" class="resp-column no-border">@{{regular_detail.r_count}}</x-bs.td-sp>
        <x-bs.td-sp caption="教科" class="not-center resp-column no-border wide">@{{regular_detail.curriculum_name}}</x-bs.td-sp>
        <td class="resp-clear"></td>
    </tr>

</x-bs.table>

@overwrite