@extends('adminlte::page')

@section('title', (request()->routeIs('desired_mng-edit')) ? '受験校編集' : '受験校登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('desired_mng-edit'))
@section('parent_page2', route('desired_mng', $editData['sid']))
@section('parent_page_title2', '受験校一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の生徒の受験校の{{(request()->routeIs('desired_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="志望順" id="order" :select2=true :editData="$editData">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
    </x-input.select>

    <x-button.list-dtl caption="学校検索"/>
    <x-input.text caption="学校名" id="school" :rules=$rules :editData=$editData/>
    <x-input.text caption="学部・学科名" id="faculty_department" :rules=$rules :editData=$editData/>
    <x-input.date-picker caption="受験日" id="exam_date" :editData=$editData />
    <x-input.select caption="合否" id="pass_fail" :select2=true :editData="$editData">
        <option value="1">合格</option>
        <option value="2">不合格</option>
        <option value="3">その他</option>
    </x-input.select>

    <x-input.textarea caption="備考" id="remarks" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('desired_mng-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('desired_mng', $editData['sid'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
            @endif

            @if (request()->routeIs('desired_mng-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.school_search-modal')

@stop