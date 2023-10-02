@extends('pages.common.modal')

@section('modal-body')

{{------------------------}}
{{-- モック用モーダル画面--}}
{{------------------------}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>開始日</th>
        <td>2022/04/01</td>
    </tr>
    <tr>
        <th>終了日</th>
        <td>2023/03/31</td>
    </tr>
    <tr>
        <th>月額</th>
        <td>16,390</td>
    </tr>
    <tr>
        <th>契約情報</th>
        <td>月4回 60分 個別（中学1･2年生）料金</td>
    </tr>

</x-bs.table>

<x-bs.form-title>契約詳細</x-bs.form-title>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th>講師名</th>
        <th>曜日</th>
        <th>開始時刻</th>
        <th>授業時間</th>
        <th>回数</th>
        <th>教科</th>
    </x-slot>

    <tr>
        <x-bs.td-sp caption="講師名">CWテスト教師１０１</x-bs.td-sp>
        <x-bs.td-sp caption="曜日" class="resp-column">月</x-bs.td-sp>
        <x-bs.td-sp caption="開始時刻" class="resp-column">16:00</x-bs.td-sp>
        <x-bs.td-sp caption="授業時間" class="resp-column">60分</x-bs.td-sp>
        <td class="resp-clear"></td>
        <x-bs.td-sp caption="回数" class="resp-column no-border">4</x-bs.td-sp>
        <x-bs.td-sp caption="教科" class="not-center resp-column no-border wide">数学</x-bs.td-sp>
        <td class="resp-clear"></td>
    </tr>

</x-bs.table>


{{------------------------}}
{{-- 本番用モーダル画面--}}
{{------------------------}}
{{-- テーブル --}}
{{-- <x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">校舎</th>
        <td>@{{item.dtl_room_name}}</td>
    </tr>
    <tr>
        <th>開始日</th>
        <td>@{{$filters.formatYmd(item.dtl_startdate)}}</td>
    </tr>
    <tr>
        <th>終了日</th>
        <td>@{{$filters.formatYmd(item.dtl_enddate)}}</td>
    </tr>
    <tr>
        <th>月額</th>
        <td>@{{$filters.toLocaleString(item.dtl_tuition)}}</td>
    </tr>
    <tr>
        <th>契約情報</th>
        <td>@{{item.dtl_regular_summary}}</td>
    </tr>

</x-bs.table>

<x-bs.form-title>契約詳細</x-bs.form-title> --}}

{{-- 最大10件なのでページネータなし --}}
{{-- <x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th>講師名</th>
        <th>曜日</th>
        <th>開始時刻</th>
        <th>授業時間</th>
        <th>回数</th>
        <th>教科</th>
    </x-slot>

    <tr v-for="regular_detail in item.regular_details" v-cloak>
        <x-bs.td-sp caption="講師名">@{{regular_detail.teacher_name}}</x-bs.td-sp>
        <x-bs.td-sp caption="曜日" class="resp-column">@{{regular_detail.weekday}}</x-bs.td-sp>
        <x-bs.td-sp caption="開始時刻" class="resp-column">@{{$filters.formatHm(regular_detail.start_time)}}</x-bs.td-sp>
        <x-bs.td-sp caption="授業時間" class="resp-column">@{{regular_detail.r_minutes}}分</x-bs.td-sp>
        <td class="resp-clear"></td>
        <x-bs.td-sp caption="回数" class="resp-column no-border">@{{regular_detail.r_count}}</x-bs.td-sp>
        <x-bs.td-sp caption="教科" class="not-center resp-column no-border wide">@{{regular_detail.curriculum_name}}</x-bs.td-sp>
        <td class="resp-clear"></td>
    </tr>

</x-bs.table> --}}

@overwrite