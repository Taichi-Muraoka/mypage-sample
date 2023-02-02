@extends('adminlte::page')

@section('title', 'カレンダー')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('tutor_mng-calendar-new', $editData['tid']) }}" caption="打ち合わせ登録" :small=true />
    </x-slot>

    <x-slot name="card_title">
        {{$name}}
    </x-slot>
    <div id="calendar"></div>

    {{-- hidden カレンダー用--}}
    <x-input.hidden id="tid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.tutor_mng_calendar-modal')

@stop