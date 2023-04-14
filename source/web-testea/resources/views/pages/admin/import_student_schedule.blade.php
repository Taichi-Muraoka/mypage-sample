@extends('adminlte::page')

@section('title', '生徒スケジュール取込')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <x-input.file caption="生徒スケジュール取込" id="upload_file" />

    <x-bs.callout>
        ファイル形式: Zip形式
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop