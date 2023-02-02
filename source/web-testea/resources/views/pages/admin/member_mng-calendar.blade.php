@extends('adminlte::page')

@section('title', 'カレンダー')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">
    <x-slot name="card_title">
        {{ $name }}
    </x-slot>

    {{-- hidden カレンダー用--}}
    <x-input.hidden id="sid" :editData=$editData />

    <div id="calendar"></div>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.member_mng_calendar-modal')

@stop