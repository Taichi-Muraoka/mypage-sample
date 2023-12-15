@extends('adminlte::page')

@section('title', '超過勤務者一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 From" id="target_date_from" :editData=$editData/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 To" id="target_date_to" :editData=$editData/>
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">講師No</th>
            <th width="15%">講師名</th>
            <th width="15%">日付</th>
            <th width="15%">合計時間</th>
            <th width="15%">超過労働</th>
            <th width="15%">深夜</th>
            <th width="15%">深夜超過労働</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.tutor_id}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>@{{$filters.formatYmd(item.target_date)}}</td>
            <td>@{{item.sum_minites}}</td>
            <td>@{{item.over_time}}</td>
            <td></td>
            <td></td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop