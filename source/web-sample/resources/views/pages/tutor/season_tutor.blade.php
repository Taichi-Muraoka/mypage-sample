@extends('adminlte::page')

@section('title', '特別期間講習 日程連絡一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('season_tutor-new', $seasonCd) }}" caption="日程登録" btn="btn-primary" :disabled=$newBtnDisabled/>
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">連絡日</th>
            <th>特別期間名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="連絡日">@{{$filters.formatYmd(item.apply_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="特別期間名">@{{item.year}}年@{{item.season_name}}</x-bs.td-sp>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_tutor-detail', '') }}/' + item.season_tutor_id" caption="詳細" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

@stop
