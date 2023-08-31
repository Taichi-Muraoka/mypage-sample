@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_grade_subject-edit')) ? '成績科目マスタデータ編集' : '成績科目マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_grade_subject-edit'))
    {{-- 編集時 --}}
    <p>以下の成績科目情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>成績科目情報の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="成績科目コード" id="g_subject_cd" :rules=$rules :editData=$editData/>
    <x-input.select caption="学校区分" id="school_kind" :select2=true :editData=$editData>
        <option value="1">小学校</option>
        <option value="2">中学校</option>
        <option value="3">高校</option>
    </x-input.select>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_grade_subject-edit'))
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