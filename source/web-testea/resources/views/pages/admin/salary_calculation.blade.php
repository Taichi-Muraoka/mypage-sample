@extends('adminlte::page')

@section('title', '給与算出一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">対象年月</th>
            <th>確定日</th>
            <th>状態</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023年03月</td>
            <td>2023/03/31</td>
            <td>集計済</td>
            <td>
                <x-button.list-dtl href="{{ route('salary_calculation-detail', 1) }}"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop