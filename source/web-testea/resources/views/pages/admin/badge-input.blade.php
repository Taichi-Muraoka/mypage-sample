@extends('adminlte::page')

@section('title', (request()->routeIs('badge-edit')) ? 'バッジ付与編集' : 'バッジ付与登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('badge-edit'))
@section('parent_page2', route('badge', $editData['sid']))
@section('parent_page_title2', 'バッジ付与一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のバッジ付与情報の{{(request()->routeIs('badge-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">CWテスト生徒１</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="バッジ種別" id="badge_kind" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>通塾</option>
        <option value="2">成績</option>
        <option value="3">紹介</option>
    </x-input.select>

    <x-input.select caption="校舎" id="roomcd" :select2=true :editData="$editData">
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">本郷</option>
    </x-input.select>

    <x-input.date-picker caption="付与日" id="authorization_date" :editData=$editData />

    <x-input.select caption="担当者" id="adm_user" :select2=true :editData="$editData">
        <option value="1">鈴木　花子</option>
        <option value="2">山田　太郎</option>
    </x-input.select>

    <x-input.textarea caption="認定理由" id="reason" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="badge_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('badge-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('badge', $editData['sid'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
            @endif

            @if (request()->routeIs('badge-edit'))
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

@stop