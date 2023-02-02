@extends('adminlte::page')

@section('title', '模試申込編集')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('trial_mng-entry', $tmid))
@section('parent_page_title', '模試申込者一覧')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の模試申込について変更を行います。</p>

    {{-- hidden --}}
    <x-input.hidden id="trial_apply_id" :editData=$editData />

    <x-input.date-picker caption="申込日" id="apply_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-input.select id="tmid" caption="模試名" :select2=true :mastrData=$trials :editData=$editData />

    <x-input.select id="apply_state" caption="ステータス" :select2=true :mastrData=$states :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('trial_mng-entry', $tmid)}}" />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop