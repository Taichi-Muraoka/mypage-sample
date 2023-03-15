@extends('adminlte::page')

@section('title', 'イベントカレンダー')

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">

    {{-- hidden カレンダー用--}}
    <x-input.hidden id="curDate" :editData=$editData />

    <div id="calendar"></div>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.event_calendar-modal')

@stop