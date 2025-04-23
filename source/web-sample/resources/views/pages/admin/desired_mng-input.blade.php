@extends('adminlte::page')

@section('title', (request()->routeIs('desired_mng-edit')) ? '受験校編集' : '受験校登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['student_id']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('desired_mng-edit'))
@section('parent_page2', route('desired_mng', $editData['student_id']))
@section('parent_page_title2', '受験校一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の生徒の受験校の{{(request()->routeIs('desired_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$name}}</p>

    <x-input.select id="exam_year" caption="受験年度" :select2=true :mastrData=$examYearList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.select id="priority_no" caption="志望順" :select2=true :mastrData=$priorityList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.modal-select caption="受験校" id="school_cd" btnCaption="学校検索" :editData=$editData />
    <x-input.text caption="学部・学科名" id="department_name" :rules=$rules :editData=$editData />
    <x-input.text caption="受験日程名" id="exam_name" :rules=$rules :editData=$editData />
    <x-input.date-picker caption="受験日" id="exam_date" :editData=$editData />
    <x-input.select id="result" caption="合否" :select2=true :mastrData=$resultList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.textarea caption="備考" id="memo" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="student_id" :editData=$editData />
    <x-input.hidden id="student_exam_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('desired_mng-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('desired_mng', $editData['student_id'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['student_id'])}}" />
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