@extends('adminlte::page')

@section('title', '教室カレンダー')

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">

    <x-bs.card :form=true>
        <x-bs.row>
            <x-bs.col2>
                {{-- 検索や未選択を非表示にする --}}
                <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                    onChange="selectChangeRoom()" :select2Search=false :blank=false />
            </x-bs.col2>
        </x-bs.row>
        {{-- hidden カレンダー用--}}
        <x-input.hidden id="target_date" :editData=$editData/>
    </x-bs.card>

    <div id="calendar"></div>

</x-bs.card>

{{-- モーダル(スケジュール詳細モーダル) --}}
@include('pages.admin.modal.room_calendar-modal')

@stop