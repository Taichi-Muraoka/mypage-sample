@extends('adminlte::page')

@section('title', 'イベント申込編集')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('event_mng-entry', $eventId))

@section('parent_page_title', 'イベント申込者一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下のイベント申込について変更を行います。</p>

    {{-- hidden --}}
    <x-input.hidden id="event_apply_id" :editData=$editData />

    <x-input.date-picker caption="申込日" id="apply_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-input.select id="event_id" caption="イベント名" :select2=true :mastrData=$events :editData=$editData />

    <x-input.select id="members" caption="参加人数" :mastrData=$members :editData=$editData :select2=true :select2Search=false />

    <x-input.select id="changes_state" caption="ステータス" :select2=true :mastrData=$states :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('event_mng-entry', $eventId)}}" />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop