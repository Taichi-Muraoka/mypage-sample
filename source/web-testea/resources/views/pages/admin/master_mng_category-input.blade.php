@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_category-edit')) ? '授業単元分類マスタデータ編集' : '授業単元分類マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_category-edit'))
    {{-- 編集時 --}}
    <p>以下の授業単元分類情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>授業単元分類情報の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="単元分類コード　(学年コード(2桁) + 教材科目コード(3桁) + 連番(2桁))" id="unit_category_cd" :rules=$rules :editData=$editData/>
    <x-input.select caption="学年" id="grade_cd" :select2=true :editData=$editData>
        <option value="7">07（中1）</option>
        <option value="8">08（中2）</option>
        <option value="9">09（中3）</option>
    </x-input.select>
    <x-input.select caption="教材科目コード" id="t_subject_cd" :select2=true :editData=$editData>
        <option value="101">101（英語）</option>
        <option value="102">102（数学）</option>
        <option value="103">103（国語）</option>
    </x-input.select>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_category-edit'))
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