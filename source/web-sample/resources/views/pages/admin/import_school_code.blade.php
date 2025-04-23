@extends('adminlte::page')

@section('title', '学校コード取込')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>文部科学省の学校コード情報をシステムに取り込みます。<br>
        文部科学省のHPから学校コード一覧のCSVファイルをダウンロードし、Zip形式で圧縮したファイルを指定してください。
    </p>

    <x-input.file caption="学校コード取込" id="upload_file" />

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