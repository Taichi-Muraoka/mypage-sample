@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_course-edit')) ? 'コースマスタデータ編集' : 'コースマスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_course-edit'))
    {{-- 編集時 --}}
    <p>以下のコース情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>コース情報の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="コースコード" id="course_cd" :rules=$rules :editData=$editData/>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="略称" id="short_name" :rules=$rules :editData=$editData/>
    <x-input.select id="course_kind" caption="コース種別" :select2=true :mastrData=$courseKindList :editData=$editData
        :select2Search=false :blank=false />
    <x-input.select id="summary_kind" caption="給与集計種別" :select2=true :mastrData=$summaryKindList :editData=$editData
        :select2Search=false :blank=false />

    {{-- hidden --}}
    <x-input.hidden id="_course_cd" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_course-edit'))
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