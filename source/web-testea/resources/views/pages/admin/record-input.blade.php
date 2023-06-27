@extends('adminlte::page')

@section('title', (request()->routeIs('record-edit')) ? '連絡記録編集' : '連絡記録登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('record-edit'))
@section('parent_page2', route('record', $editData['sid']))
@section('parent_page_title2', '連絡記録一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の連絡記録の{{(request()->routeIs('record-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">CWテスト生徒１</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="記録種別" id="karte_kind" :select2=true :editData="$editData">
        <option value="1">面談記録</option>
        <option value="2">電話記録</option>
        <option value="3">その他</option>
    </x-input.select>

    <x-input.select caption="対応校舎" id="roomcd" :select2=true :editData="$editData">
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">本郷</option>
    </x-input.select>

    <x-input.textarea caption="内容" id="karte_text" :editData=$editData />

    <x-input.date-picker caption="対応日" id="received_date" :editData=$editData />

    <x-input.time-picker caption="対応時刻" id="received_time" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('record-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('record', $editData['sid'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
            @endif

            @if (request()->routeIs('record-edit'))
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