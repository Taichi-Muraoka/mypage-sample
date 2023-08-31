@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_booth-edit')) ? 'ブースマスタデータ編集' : 'ブースマスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_booth-edit'))
    {{-- 編集時 --}}
    <p>以下のブース情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>ブース情報の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.select caption="校舎" id="campus_cd" :select2=true :editData=$editData>
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
    </x-input.select>
    <x-input.text caption="ブースコード" id="booth_cd" :rules=$rules :editData=$editData/>
    <x-input.select caption="用途種別" id="usage_kind" :select2=true :select2Search=false :editData=$editData >
        <option value="1">授業用</option>
        <option value="2">オンライン用</option>
        <option value="3">面談用</option>
        <option value="4">両者オンライン</option>
        <option value="5">家庭教師</option>
    </x-input.select>
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="表示順" id="disp_order" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_booth-edit'))
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