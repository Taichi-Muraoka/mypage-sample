@extends('adminlte::page')

@section('title', '請求情報一覧')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>
    <x-slot name="card_title">
        {{ $name }}
    </x-slot>
    <x-bs.card-list>

        {{-- hidden 検索一覧用--}}
        <x-input.hidden id="sid" :editData=$editData />

        {{-- テーブル --}}
        <x-bs.table :button=true>

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>請求書</th>
                <th width="5%"></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                {{-- <x-bs.td-sp caption="請求年月">@{{item.invoice_date|formatYmString}}請求書</x-bs.td-sp> --}}
                <x-bs.td-sp caption="請求年月">2023年7月分請求書</x-bs.td-sp>
                <td>
                    {{-- 請求明細 URLとIDを指定。IDはVueで指定される。 --}}
                    <x-button.list-dtl vueHref="'{{ route('member_mng-invoice', ['', '']) }}/' + item.id + '/detail/' + item.date" caption="請求情報" />
                </td>
                {{-- モックのサンプル表示のためもう一行追加（実装の際は不要） --}}
                <tr v-for="item in paginator.data" v-cloak>
                    {{-- <x-bs.td-sp caption="請求年月">@{{item.invoice_date|formatYmString}}請求書</x-bs.td-sp> --}}
                    <x-bs.td-sp caption="請求年月">2023年6月分請求書</x-bs.td-sp>
                    <td>
                        {{-- 請求明細 URLとIDを指定。IDはVueで指定される。 --}}
                        <x-button.list-dtl vueHref="'{{ route('member_mng-invoice', ['', '']) }}/' + item.id + '/detail/' + item.date" caption="請求情報" />
                    </td>
            </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>
</x-bs.card>

@stop