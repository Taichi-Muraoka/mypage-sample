@extends('adminlte::page')

@section('title', '請求情報一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>請求書</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- <x-bs.td-sp caption="請求年月">@{{item.invoice_date|formatYmString}}請求書</x-bs.td-sp> --}}
            <x-bs.td-sp caption="請求年月">2023年7月分請求書</x-bs.td-sp>
            <td>
                {{-- 請求明細 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-dtl vueHref="'{{ route('invoice-detail', '') }}/' + item.date" caption="請求情報" />
            </td>
        </tr>
        {{-- モックのサンプル表示のためもう一行追加（実装の際は不要） --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- <x-bs.td-sp caption="請求年月">@{{item.invoice_date|formatYmString}}請求書</x-bs.td-sp> --}}
            <x-bs.td-sp caption="請求年月">2023年6月分請求書</x-bs.td-sp>
            <td>
                {{-- 請求明細 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-dtl vueHref="'{{ route('invoice-detail', '') }}/' + item.date" caption="請求情報" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop