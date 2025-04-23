@extends('adminlte::page')

@section('title', 'カレンダー')

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')


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
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.room_calendar-modal')

@stop