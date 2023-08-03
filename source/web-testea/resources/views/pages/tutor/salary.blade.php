@extends('adminlte::page')

@section('title', '給与明細一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>給与明細書</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- <td>@{{item.salary_date|formatYmString}}分給与</td> --}}
            <td>2023年7月分給与</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('salary-detail', '') }}/' + item.id" caption="給与情報" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop