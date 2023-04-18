@extends('adminlte::page')

@section('title', (request()->routeIs('event_mng-edit')) ? 'イベント編集' : 'イベント登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="event_id" :editData=$editData />

    <p>イベント情報の{{(request()->routeIs('event_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.text caption="イベント名" id="name" :rules=$rules :editData=$editData />

    {{-- <x-input.select caption="学年" id="cls_cd" :select2=true :mastrData=$cls :editData=$editData /> --}}
    <x-input.select id="cls_cd" caption="学年" :select2=true >
        <option value="1">高3</option>
        <option value="2">高2</option>
        <option value="3">高1</option>
        <option value="4">中3</option>
        <option value="5">中2</option>
        <option value="6">中1</option>
    </x-input.select>

    <x-input.date-picker caption="開催日" id="event_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-bs.callout>
        複数の会場・日程がある場合は、それぞれ別のイベントとして登録してください。<br>
        イベント名に会場を含めて設定してください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('event_mng-edit'))
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