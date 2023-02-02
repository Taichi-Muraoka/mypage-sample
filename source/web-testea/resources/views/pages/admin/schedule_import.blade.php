@extends('adminlte::page')

@section('title', 'スケジュール取込')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>授業振替、模試申込により変更された生徒のスケジュールを取込みます。</p>

    <x-input.file caption="スケジュール情報ファイル" id="upload_file" />

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