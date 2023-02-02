@extends('adminlte::page')

@section('title', '模試・イベント申込')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>模試・イベントの申込を行います。</p>

    <x-input.select caption="模試・イベント種別" id="tm_event_type" :select2=true :select2Search=false :mastrData=$typeMastrData
        :editData=$typeEditData />

    <div v-cloak>

        <x-input.select caption="模試・イベント名" id="tmid" :select2=true :mastrData=$trialMastrData :editData=$trialEditData
            vShow="form.tm_event_type == {{ App\Consts\AppConst::CODE_MASTER_14_1 }}" />

        <x-input.select caption="模試・イベント名" id="event_id" :select2=true :mastrData=$eventMastrData
            :editData=$eventEditData vShow="form.tm_event_type == {{ App\Consts\AppConst::CODE_MASTER_14_2 }}" />

        <x-input.select caption="参加人数" id="members" :select2=true :select2Search=false
            vShow="form.tm_event_type == {{ App\Consts\AppConst::CODE_MASTER_14_2 }}" :mastrData=$eventMembers />

    </div>

    <x-bs.callout type="warning">
        短期講習のお申込みは、「コース変更・授業追加申請」より受付いたします。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop