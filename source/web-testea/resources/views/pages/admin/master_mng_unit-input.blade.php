@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_unit-edit')) ? '授業単元マスタデータ編集' : '授業単元マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_unit-edit'))
    {{-- 編集時 --}}
    <p>以下の授業単元情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>授業単元情報の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.select caption="学年" id="grade_cd" :select2=true :editData=$editData>
        <option value="7">中1</option>
        <option value="8">中2</option>
        <option value="9">中3</option>
    </x-input.select>
    <x-input.select caption="教材科目コード" id="t_subject_cd" :select2=true :editData=$editData>
        <option value="101">101（英語）</option>
        <option value="102">102（数学）</option>
        <option value="103">103（国語）</option>
    </x-input.select>
    <x-input.select caption="単元分類" id="unit_category_cd" :select2=true :editData=$editData>
        <option value="1">正負の数</option>
        <option value="2">方程式</option>
        <option value="3">一次関数</option>
    </x-input.select>
    <x-input.text caption="単元コード" id="unit_cd" :rules=$rules :editData=$editData/>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_unit-edit'))
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