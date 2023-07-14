@extends('adminlte::page')

@section('title', '特別期間講習 日程連絡一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('season_tutor-new') }}" :small=true caption="日程登録" />
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
        <tr>
            <x-bs.td-sp caption="連絡日">2023/03/05</x-bs.td-sp>
            <x-bs.td-sp caption="特別期間名">2023年春期</x-bs.td-sp>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_tutor-detail', '') }}/' + 1" caption="詳細" />
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="連絡日">2022/12/05</x-bs.td-sp>
            <x-bs.td-sp caption="特別期間名">2022年冬期</x-bs.td-sp>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_tutor-detail', '') }}/' + 1" caption="詳細" />
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="連絡日">2022/07/05</x-bs.td-sp>
            <x-bs.td-sp caption="特別期間名">2022年夏期</x-bs.td-sp>
            <td>
                <x-button.list-dtl vueHref="'{{ route('season_tutor-detail', '') }}/' + 1" caption="詳細" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop