@extends('adminlte::page')

@section('title', (request()->routeIs('room_holiday-edit')) ? '休業日編集' : '休業日登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の休業日の{{(request()->routeIs('room_holiday-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :rules=$rules :editData=$editData />

    <x-input.date-picker caption="休業日" id="holiday_date" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="room_holiday_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('room_holiday-edit'))
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