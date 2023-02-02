@extends('adminlte::page')

@section('title', (request()->routeIs('training_mng-edit')) ? '研修教材編集' : '研修教材登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- カード --}}
<x-bs.card :form="true">

    {{-- hidden --}}
    <x-input.hidden id="trn_id" :editData=$editData />

    <p>研修教材の{{(request()->routeIs('training_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.select caption="形式" id="trn_type" :select2=true :mastrData=$trainingType :editData=$editData />

    <x-input.text caption="内容" id="text" :rules=$rules :editData=$editData />

    @if (request()->routeIs('training_mng-edit'))
    <x-input.date-picker caption="登録日" id="regist_time" :editData=$editData />
    @endif

    <x-input.date-picker caption="期限" id="limit_date" :editData=$editData />

    <x-input.date-picker caption="公開日" id="release_date" :editData=$editData />

    <p class="text-muted" v-show="form.trn_type == ''">研修形式を選択してください。</p>

    {{-- 資料はfile_docという名前にした --}}
    <x-input.file caption="資料" id="file_doc" v-show="form.trn_type == {{ App\Consts\AppConst::CODE_MASTER_12_1 }}"
        :editData=$editData />
    <span v-show="form.trn_type == {{ App\Consts\AppConst::CODE_MASTER_12_1 }}">
        <x-bs.callout>
            最大ファイルアップロードサイズ：{{ini_get('upload_max_filesize')}}
        </x-bs.callout>
    </span>

    <x-input.text caption="動画リンク" id="url" v-show="form.trn_type == {{ App\Consts\AppConst::CODE_MASTER_12_2 }}"
        :rules=$rules :editData=$editData />


    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('training_mng-edit'))
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