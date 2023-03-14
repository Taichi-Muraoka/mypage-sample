@extends('adminlte::page')

@section('title', 'イベント申込')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>イベントの申込を行います。</p>

    <div v-cloak>
        <x-input.select caption="イベント名" id="event_id" :select2=true :mastrData=$eventMastrData
            :editData=$eventEditData />

        <x-input.select caption="参加人数" id="members" :select2=true :select2Search=false
             :mastrData=$eventMembers />
    </div>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop