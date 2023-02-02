@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-calendar-edit')) ? '教師打ち合わせ予定編集' : '教師打ち合わせ予定登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('tutor_mng-calendar', $editData['tid']))

@section('parent_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />
    <x-input.hidden id="tutor_schedule_id" :editData=$editData />

    <x-slot name="card_title">
        {{$name}}
    </x-slot>

    <p>教師打ち合わせ予定の{{(request()->routeIs('tutor_mng-calendar-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.text caption="打ち合わせ名" id="title" :rules=$rules :editData=$editData />

    <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData />

    <x-input.date-picker caption="開催日" id="start_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
            <x-button.back url="{{ route('tutor_mng-calendar', $editData['tid']) }}" />

            @if (request()->routeIs('tutor_mng-calendar-edit'))
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