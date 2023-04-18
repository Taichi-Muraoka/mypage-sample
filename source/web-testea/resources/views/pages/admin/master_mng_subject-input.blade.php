@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_subject-edit')) ? '教科マスタデータ編集' : '教科マスタデータ新規登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_subject-edit'))
    {{-- 編集時 --}}
    <p>以下の教科情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>教科の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="コード" id="code" :rules=$rules :editData=$editData/>
    <x-input.select caption="学校区分" id="classification_school" :select2=true :editData=$editData>
        <option value="1">小</option>
        <option value="2">中</option>
        <option value="3">高</option>
    </x-input.select>
    <x-input.text caption="教科名" id="name_subject" :rules=$rules :editData=$editData/>
    <x-input.text caption="表示順" id="display_order" :rules=$rules :editData=$editData/>

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_subject-edit'))
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