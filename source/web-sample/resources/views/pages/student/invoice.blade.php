@extends('adminlte::page')

@section('title', '請求情報一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>請求書</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="請求年月">@{{$filters.formatYmString(item.invoice_date)}}請求書</x-bs.td-sp>
            <td>
                {{-- 請求明細 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-dtl vueHref="'{{ route('invoice-detail', '') }}/' + item.date" caption="請求情報" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

@stop