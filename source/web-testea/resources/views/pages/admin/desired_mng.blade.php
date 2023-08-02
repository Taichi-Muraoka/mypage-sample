@extends('adminlte::page')

@section('title', '受験校一覧')

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
            <th>受験年度</th>
            <th>志望順</th>
            <th>受験校</th>
            <th>学部・学科名</th>
            <th>受験日程名</th>
            <th>受験日</th>
            <th>合否</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2022</td>
            <td>1</td>
            <td>青山高等学校</td>
            <td>普通科</td>
            <td>A日程</td>
            <td>2023/03/03</td>
            <td>合格</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('desired_mng-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2022</td>
            <td>2</td>
            <td>成城第二高等学校</td>
            <td>特進科</td>
            <td>B日程</td>
            <td>2023/02/01</td>
            <td>合格</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('desired_mng-edit', 2) }}" />
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
@include('pages.admin.modal.desired_mng-modal')

@stop