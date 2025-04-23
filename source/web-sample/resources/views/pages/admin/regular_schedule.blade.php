@extends('adminlte::page')

@section('title', 'Default Week')

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">

    <x-bs.card :form=true id="top">
        <x-bs.row>
            <x-bs.col2>
                {{-- 検索や未選択を非表示にする --}}
                <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                    onChange="selectChangeRoom()" :select2Search=false :blank=false />
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
        <x-input.hidden id="validate_msg_area" validateErr=true />
        <div class="d-flex justify-content-end">
            <x-button.submit-new  caption="一括登録" />
        </div>
        <div>
            <x-button.submit-href caption="月" icon="" btn="secondary" class="mr-2" href="#calendar1" />
            <x-button.submit-href caption="火" icon="" btn="secondary" class="mr-2" href="#calendar2" />
            <x-button.submit-href caption="水" icon="" btn="secondary" class="mr-2" href="#calendar3" />
            <x-button.submit-href caption="木" icon="" btn="secondary" class="mr-2" href="#calendar4" />
            <x-button.submit-href caption="金" icon="" btn="secondary" class="mr-2" href="#calendar5" />
            <x-button.submit-href caption="土" icon="" btn="secondary" class="mr-2" href="#calendar6" />
            <x-button.submit-href caption="日" icon="" btn="secondary" class="mr-2" href="#calendar7" />
        </div>

    </x-bs.card>

    <div id="calendar1"></div>
    <div id="calendar2"></div>
    <div id="calendar3"></div>
    <div id="calendar4"></div>
    <div id="calendar5"></div>
    <div id="calendar6"></div>
    <div id="calendar7"></div>

</x-bs.card>

{{-- モーダル(スケジュール詳細モーダル) --}}
@include('pages.admin.modal.regular_schedule-modal')

@stop
