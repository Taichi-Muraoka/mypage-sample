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
    <p>授業教材の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="教材ID" id="text_id" :rules=$rules :editData=$editData/>
    <x-input.select caption="学年" id="grade_cd" :select2=true :editData=$editData>
        <option value="1">小1</option>
        <option value="2">小2</option>
        <option value="3">小3</option>
    </x-input.select>
    <x-input.select caption="科目" id="subject_cd" :select2=true :editData=$editData>
        <option value="1">国語</option>
        <option value="2">数学</option>
        <option value="3">英語</option>
    </x-input.select>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_text-edit'))
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