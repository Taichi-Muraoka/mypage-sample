@extends('adminlte::page')

@section('title', 'ギフトカード一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">付与日</th>
            <th>ギフトカード名</th>
            <th width="15%">使用期間<br>開始日</th>
            <th width="15%">使用期間<br>終了日</th>
            <th width="15%">ステータス</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="付与日">@{{item.grant_time|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="ギフトカード名">@{{item.card_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="使用期間 開始日">@{{item.term_start|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="使用期間 終了日">@{{item.term_end|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">@{{item.card_state}}</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.card_id']" />
                <x-button.list-edit caption="使用" vueHref="'{{ route('card-use', ['','']) }}/' + item.card_id"
                    vueDisabled="item.disabled" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.card-modal')

@stop