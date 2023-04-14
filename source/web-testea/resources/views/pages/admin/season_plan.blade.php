@extends('adminlte::page')

@section('title', '特別期間講習自動コマ組み状況一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">特別期間</th>
            <th>確定日</th>
            <th>状態</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023年春期</td>
            <td></td>
            <td>コマ組み完了</td>
            <td>
                <x-button.list-dtl href="{{ route('season_plan-autoexec', 1) }}"/>
            </td>
        </tr>
        <tr>
            <td>2022年冬期</td>
            <td>2022/12/10</td>
            <td>確定済</td>
            <td>
                <x-button.list-dtl href="{{ route('season_plan-autoexec', 1) }}"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop