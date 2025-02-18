@extends('adminlte::page')

@section('title','システムマスタデータ編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のシステム情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 共通フォーム --}}
    <div v-cloak>
        <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData />

        <x-input.text caption="値（数値）" id="value_num" :rules=$rules :editData=$editData
            vShow="form.datatype_kind == {{ App\Consts\AppConst::SYSTEM_DATATYPE_1 }}" />

        <x-input.text caption="値（文字列）" id="value_str" :rules=$rules :editData=$editData
            vShow="form.datatype_kind == {{ App\Consts\AppConst::SYSTEM_DATATYPE_2 }}" />

        <x-input.date-picker caption="値（日付）" id="value_date" :rules=$rules :editData=$editData
            vShow="form.datatype_kind == {{ App\Consts\AppConst::SYSTEM_DATATYPE_3 }}" />

        <x-input.select vShow="form.datatype_kind == {{ App\Consts\AppConst::SYSTEM_DATATYPE_4 }}"
            caption="値（可否フラグ）" id="value_flg" :select2=true :mastrData=$flgList :editData="$editData" :select2Search=false :blank=false />
    </div>

    {{-- hidden --}}
    <x-input.hidden id="key_id" :editData=$editData />
    <x-input.hidden id="datatype_kind" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            <div class="d-flex justify-content-end">
                <x-button.submit-edit />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop