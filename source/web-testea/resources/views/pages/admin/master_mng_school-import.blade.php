@extends('adminlte::page')

@section('title', '校舎マスタファイル取込')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <x-input.file caption="校舎マスタファイル" id="upload_file" />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

    <x-bs.callout>
        ファイル形式: Zip形式
    </x-bs.callout>

</x-bs.card>

@stop