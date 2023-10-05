@extends('adminlte::page')

@section('title', 'バッジ付与情報一覧')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $sid))

@section('parent_page_title', '生徒カルテ')

@section('content')

<x-bs.card>
    <x-slot name="card_title">
    {{$name}}
    </x-slot>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">認定日</th>
            <th width="15%">バッジ種別</th>
            <th width="15%">校舎</th>
            <th width="15%">担当者名</th>
            <th>認定理由</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.authorization_date)}}</td>
            <td>@{{item.kind_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.admin_name}}</td>
            <td>@{{item.reason}}</td>
            <td>
                <x-button.list-edit :vueDataAttr="['id' => 'item.badge_id']" />
            </td>
        </tr>

    </x-bs.table>
</x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('member_mng-detail', $sid)}}" />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.record-modal')

@stop