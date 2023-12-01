@extends('adminlte::page')

@section('title', '連絡記録一覧')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $sid))

@section('parent_page_title', '生徒カルテ')

@section('content')

<x-bs.card>
    <x-slot name="card_title">
        {{$student_name}}
    </x-slot>

{{-- 結果リスト --}}
<x-bs.card-list>
    {{-- hidden --}}
    <x-input.hidden id="student_id" :editData=$editData />

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">対応日時</th>
            <th>記録種別</th>
            <th>対応校舎</th>
            <th>担当者名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.received_date)}}&nbsp;@{{item.received_time}}</td>
            <td>@{{item.kind_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.admin_name}}</td>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.record_id']" />
                <x-button.list-edit vueHref="'{{ route('record-edit', '') }}/' + item.record_id" />
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