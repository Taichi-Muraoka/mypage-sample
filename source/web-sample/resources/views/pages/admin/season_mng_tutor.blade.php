@extends('adminlte::page')

@section('title', '特別期間講習 講師日程一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="season_cd" caption="特別期間" :select2=true :mastrData=$seasonList :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師名" :select2=true :mastrData=$tutors :editData=$editData
                :rules=$rules :select2Search=true :blank=true />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">連絡日</th>
            <th>特別期間名</th>
            <th>講師名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.year}}年@{{item.season_name}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_mng_tutor-detail', '') }}/' + item.season_tutor_id" caption="詳細" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

@stop
