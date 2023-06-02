@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_campus-edit')) ? '校舎マスタデータ編集' : '校舎マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_campus-edit'))
    {{-- 編集時 --}}
    <p>以下の校舎情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>校舎の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="校舎コード" id="campus_cd" :rules=$rules :editData=$editData/>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="表示名" id="disp_name" :rules=$rules :editData=$editData/>
    <x-input.text caption="略称" id="short_name" :rules=$rules :editData=$editData/>
    <x-input.text caption="表示順" id="disp_order" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_campus-edit'))
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