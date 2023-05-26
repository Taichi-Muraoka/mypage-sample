@extends('adminlte::page')

@section('title','システムマスタデータ編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のシステム情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 共通フォーム --}}
    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="値（数値）" id="value_num" :rules=$rules :editData=$editData/>
    <x-input.text caption="値（文字列）" id="value_str" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            <div class="d-flex justify-content-end">
                <x-button.submit-edit />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop