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
        {{$name}}
    </x-slot>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('record-new', $sid) }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">対応日時</th>
            <th>記録種別</th>
            <th>校舎</th>
            <th>担当者名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10 17:00</td>
            <td>面談記録</td>
            <td>久我山</td>
            <td>山田　太郎</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('record-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2023/01/09 19:30</td>
            <td>電話記録</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('record-edit', 2) }}" />
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