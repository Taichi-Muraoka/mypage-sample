@extends('adminlte::page')

@section('title', '特別期間講習 講師提出スケジュール一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="season" caption="特別期間" :select2=true>
                <option value="1">2023年春期</option>
                <option value="2">2022年冬期</option>
                <option value="3">2022年夏期</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="講師名" :rules=$rules />
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
        <tr>
            <td>2023/03/05</td>
            <td>2023年春期</td>
            <td>CWテスト教師１０１</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_mng_tutor-detail', '') }}/' + 1" caption="詳細" />
            </td>
        </tr>
        <tr>
            <td>2023/03/04</td>
            <td>2023年春期</td>
            <td>CWテスト教師１０２</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_mng_tutor-detail', '') }}/' + 1" caption="詳細" />
            </td>
        </tr>


    </x-bs.table>
</x-bs.card-list>

@stop