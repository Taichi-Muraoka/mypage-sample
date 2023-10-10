@extends('adminlte::page')

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">
    <div id="calendar"></div>
</x-bs.card>

{{-- モーダル --}}
@include('pages.mypage-common.modal.calendar-modal')

@stop