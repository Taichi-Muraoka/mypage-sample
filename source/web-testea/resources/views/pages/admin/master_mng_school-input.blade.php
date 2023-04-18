@extends('adminlte::page')

@section('title', '校舎マスタデータ新規登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_school-edit'))
    {{-- 編集時 --}}
    <p>以下の校舎情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>校舎の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="コード" id="code" :rules=$rules :editData=$editData/>
    <x-input.text caption="校舎名" id="name_school" :rules=$rules :editData=$editData/>
    <x-input.text caption="校舎表示名" id="name_school_display" :rules=$rules :editData=$editData/>
    <x-input.text caption="校舎略称" id="name_school_abbreviation" :rules=$rules :editData=$editData/>
    <x-input.text caption="表示順" id="display_order" :rules=$rules :editData=$editData/>

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_school-edit'))
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