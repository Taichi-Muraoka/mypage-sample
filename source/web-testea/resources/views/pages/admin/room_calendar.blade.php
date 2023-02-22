@extends('adminlte::page')

@section('title', '教室カレンダー')

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">

    <x-bs.card :form=true>
        <x-bs.row>
            <x-bs.col2>
                @can('roomAdmin')
                {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
                <x-input.select id="roomcd" :select2=true :mastrData=$rooms :editData=$editData
                    :select2Search=false :blank=false />
                @else
                <x-input.select id="roomcd" :select2=true onChange="selectChangeRoom()" :mastrData=$rooms :editData=$editData />
                @endcan
            </x-bs.col2>
        </x-bs.row>
        {{-- hidden カレンダー用--}}
        <x-input.hidden id="curDate" :editData=$editData />
        <x-input.hidden id="testDate"  />
    </x-bs.card>

    <div id="calendar"></div>

</x-bs.card>

{{-- モーダル(スケジュール詳細モーダル) --}}
@include('pages.admin.modal.room_calendar-modal')

@stop