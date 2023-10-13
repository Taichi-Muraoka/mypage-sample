@extends('adminlte::page')

@section('title', (request()->routeIs('badge-edit')) ? 'バッジ付与情報編集' : 'バッジ付与情報登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['student_id']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('badge-edit'))
@section('parent_page2', route('badge', $editData['student_id']))
@section('parent_page_title2', 'バッジ付与情報一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のバッジ付与情報の{{(request()->routeIs('badge-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$name}}</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select id="badge_type" caption="バッジ種別" :select2=true onChange="selectChangeGetTemplate" :mastrData=$kindList :editData=$editData
        :select2Search=false :blank=true />

    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=false />

    <x-input.text caption="認定理由" id="reason" :rules=$rules :editData=$editData/>

    {{-- hidden --}}
    <x-input.hidden id="badge_id" :editData=$editData />
    <x-input.hidden id="student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('badge-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('badge', $editData['student_id'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['student_id'])}}" />
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