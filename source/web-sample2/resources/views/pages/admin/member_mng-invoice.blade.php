@extends('adminlte::page')

@section('title', '請求情報一覧')

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

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
                <x-bs.td-sp caption="請求年月">@{{$filters.formatYmString(item.invoice_date)}}請求書</x-bs.td-sp>
                <td>
                    {{-- 請求明細 URLとIDを指定。IDはVueで指定される。 --}}
                    <x-button.list-dtl vueHref="'{{ route('member_mng-invoice', ['', '']) }}/' + item.id + '/detail/' + item.date" caption="請求情報" />
                </td>
            </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
        </div>
    </x-slot>
</x-bs.card>

@stop