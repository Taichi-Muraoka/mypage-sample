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
    <x-input.select id="grade_cd" caption="学年" :select2=true onChange="selectChangeGetCategory" :mastrData=$grades
        :editData=$editData :select2Search=false :blank=true />
    <x-input.select id="t_subject_cd" caption="教材科目コード" :select2=true onChange="selectChangeGetCategory"
        :mastrData=$subjects :editData=$editData :select2Search=false :blank=true />
    <x-input.select id="unit_category_cd" caption="単元分類" :select2=true :editData=$editData :select2Search=true
        :blank=true>
        <option v-for="item in selectGetItemCategory.categories" :value="item.code">
            @{{ item.value }}
        </option>
    </x-input.select>
    <x-input.text caption="単元コード(2桁・その他は99)" id="unit_cd" :rules=$rules :editData=$editData />
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="unit_id" :editData=$editData />
    <x-input.hidden id="_unit_category_cd" :editData=$editData />

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