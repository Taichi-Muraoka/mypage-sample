@extends('adminlte::page')

@section('title', '授業報告書一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>授業日・時限</th>
            <th>校舎</th>
            <th>コース</th>
            <th>講師名</th>
            <th width="5%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="授業日・時限">2023/05/15 4限</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
            <x-bs.td-sp caption="コース">個別指導</x-bs.td-sp>
            <x-bs.td-sp caption="講師名">CWテスト教師１０１</x-bs.td-sp>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>

        {{-- 本番用処理 --}}
        {{-- テーブル行 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="授業日時">@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="コース"></x-bs.td-sp>
            <x-bs.td-sp caption="講師名">@{{item.tname}}</x-bs.td-sp>
            <td> --}}
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                {{-- <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
            </td>
        </tr> --}}

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.report-modal')

@stop