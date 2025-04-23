@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_text-edit')) ? '授業教材マスタデータ編集' : '授業教材マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_text-edit'))
    {{-- 編集時 --}}
    <p>以下の授業教材情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>
        授業教材の登録を行います。<br>
    </p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="教材コード　(学年コード(2桁) + 授業教科コード(3桁) + 教科連番(1桁) + 教材名連番(2桁・その他は99))" id="text_cd" :rules=$rules
        :editData=$editData />
    <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$grades :editData=$editData :select2Search=false
        :blank=true />
    <x-input.select id="l_subject_cd" caption="授業教科コード" :select2=true :mastrData=$subjects :editData=$editData
        :select2Search=false :blank=true />
    <x-input.select id="t_subject_cd" caption="教材教科コード" :select2=true :mastrData=$textSubjects :editData=$editData
        :select2Search=false :blank=true />
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="_text_cd" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_text-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete-validation />
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