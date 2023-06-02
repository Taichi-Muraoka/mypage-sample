@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_agreement-edit')) ? '契約コースマスタデータ編集' : '契約コースマスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_agreement-edit'))
    {{-- 編集時 --}}
    <p>以下の契約コース情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>契約コースの登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="契約コースコード" id="agreement_cd" :rules=$rules :editData=$editData/>

    <x-input.select caption="授業種別" id="lesson_kind" :select2=true>
        <option value="1">個別</option>
        <option value="2">集団</option>
    </x-input.select>

    <x-input.select caption="学校区分" id="school_kind" :select2=true>
        <option value="1">小</option>
        <option value="2">中</option>
        <option value="3">高</option>
        <option value="4">その他</option>
    </x-input.select>

    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>

    <x-input.text caption="金額" id="tuition" :rules=$rules :editData=$editData/>

    <x-input.text caption="単価" id="unit_tuition" :rules=$rules :editData=$editData/>

    <x-input.text caption="回数" id="count" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_agreement-edit'))
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