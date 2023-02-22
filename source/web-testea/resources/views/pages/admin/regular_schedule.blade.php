@extends('adminlte::page')

@section('title', 'レギュラースケジュール')

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
        <x-bs.row>
            <x-bs.col2>
                <x-input.date-picker caption="一括登録 From" id="date_from" />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.date-picker caption="To" id="date_to" />
            </x-bs.col2>
        </x-bs.row>
        {{-- hidden カレンダー用--}}
        <div class="d-flex justify-content-end">
            <x-button.submit-new  caption="一括登録" />
        </div>
    </x-bs.card>

    <div id="calendar1"></div>
    <div id="calendar2"></div>
    <div id="calendar3"></div>
    <div id="calendar4"></div>
    <div id="calendar5"></div>
    <div id="calendar6"></div>

</x-bs.card>

{{-- モーダル(スケジュール詳細モーダル) --}}
@include('pages.admin.modal.regular_schedule-modal')

@stop