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
        <tr>
            <td>2023/05/10</td>
            <td>紹介</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（佐藤次郎さん）</td>
            <td>
                <x-button.list-edit href="{{ route('badge-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2023/04/01</td>
            <td>通塾</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>契約期間が３年を超えた</td>
            <td>
                <x-button.list-edit href="{{ route('badge-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2022/03/20</td>
            <td>紹介</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（仙台太郎さん）</td>
            <td>
                <x-button.list-edit href="{{ route('badge-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2022/02/20</td>
            <td>成績</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>成績UP</td>
            <td>
                <x-button.list-edit href="{{ route('badge-edit', 1) }}" />
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