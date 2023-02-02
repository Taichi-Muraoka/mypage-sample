@extends('adminlte::page')

@section('title', '教師登録')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>教師採用時もしくは教師の情報変更時に教師情報を取り込みます。</p>

    <x-input.file caption="教師情報ファイル" id="upload_file" />

    <x-bs.callout>
        ファイル形式：ZIP形式
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop