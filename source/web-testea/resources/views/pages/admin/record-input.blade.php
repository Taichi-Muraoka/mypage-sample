@extends('adminlte::page')

@section('title', (request()->routeIs('record-edit')) ? '連絡記録編集' : '連絡記録登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['student_id']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('record-edit'))
@section('parent_page2', route('record', $editData['student_id']))
@section('parent_page_title2', '連絡記録一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の連絡記録の{{(request()->routeIs('record-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$student_name}}</p>

    <x-bs.form-title>担当者名</x-bs.form-title>
    <p class="edit-disp-indent">{{$manager_name}}</p>

    @can('roomAdmin')
    {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
    <x-bs.form-title>担当者名</x-bs.form-title>
    <p class="edit-disp-indent">{{$room_name}}</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>
    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData/>
    @else
    {{-- 余白 --}}
    <div class="mb-3"></div>
    {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=true />
    @endcan

    <x-input.select caption="記録種別" id="record_kind" :select2=true :mastrData=$recordKind :editData=$editData
        :select2Search=false :blank=true />

    <x-input.textarea caption="内容" id="memo" :editData=$editData />

    <x-input.date-picker caption="対応日" id="received_date" :editData=$editData />

    <x-input.time-picker caption="対応時刻" id="received_time" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />
    <x-input.hidden id="student_id" :editData=$editData/>
    <x-input.hidden id="adm_id" :editData=$editData/>
    <x-input.hidden id="record_id" :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('record-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('record', $editData['student_id'])}}" />
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