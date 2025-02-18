@extends('adminlte::page')

@section('title', (request()->routeIs('surcharge-edit')) ? '追加請求編集' : '追加請求登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>授業給以外の費用について追加請求申請を行います。</p>

    <x-input.select id="surcharge_kind" caption="請求種別" :select2=true onChange="selectChangeGet" :mastrData=$kindList
        :editData=$editData :select2Search=false :blank=true />

    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false
        :blank=true />

    <x-input.date-picker caption="実施日" id="working_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData
        vShow="form.sub_code == {{ App\Consts\AppConst::CODE_MASTER_26_SUB_8 }}" />

    <x-input.text caption="時間(分)" id="minutes" :rules=$rules :editData=$editData
        vShow="form.sub_code == {{ App\Consts\AppConst::CODE_MASTER_26_SUB_8 }}" />

    <x-input.text caption="金額" id="tuition" :rules=$rules :editData=$editData
        vShow="form.sub_code == {{ App\Consts\AppConst::CODE_MASTER_26_SUB_9 }} || form.sub_code == {{ App\Consts\AppConst::CODE_MASTER_26_SUB_10 }}" />

    <x-input.textarea caption="内容(作業・費目等)" id="comment" :editData=$editData :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="sub_code" :editData=$editData />
    <x-input.hidden id="surcharge_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('surcharge-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop