@extends('adminlte::page')

@section('title', '回数報告一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="report_date" caption="実施月" :select2=true :mastrData=$reportDate />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="教師名" id="name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">報告日</th>
            <th>教師名</th>
            <th width="15%">実施月</th>
            <th width="15%">教室</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for='item in paginator.data' v-cloak>
            <td>@{{item.regist_time|formatYmd}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.report_date|formatYm}}</td>
            <td>@{{item.room_name}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['tid' => 'item.tid', 'start_month' => 'item.report_date', 'times_report_id' => 'item.times_report_id']" />
                <x-button.list-edit vueHref="'{{ route('times_check-edit', '') }}/' + item.times_report_id" />
            </td>
        </tr>


    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.times_check-modal')
@stop