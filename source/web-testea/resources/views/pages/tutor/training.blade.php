@extends('adminlte::page')

@section('title', '研修一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">

            <th width="10%">形式</th>
            <th>内容</th>
            <th width="15%">公開日</th>
            <th width="15%">期限</th>
            <th width="10%">閲覧</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="形式">@{{item.trn_type_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="内容">@{{item.text}}</x-bs.td-sp>
            <x-bs.td-sp caption="公開日">@{{$filters.formatYmd(item.release_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="期限"><span v-if="item.limit_date">@{{$filters.formatYmd(item.limit_date)}}</span><span v-else>無期限</span></x-bs.td-sp>
            <x-bs.td-sp caption="閲覧"><span v-if="item.browse_time == null">未</span><span v-else>済</span></x-bs.td-sp>
            <td>
                <x-button.list-edit vueHref="'{{ route('training-detail', '') }}/' + item.id" caption="受講" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop