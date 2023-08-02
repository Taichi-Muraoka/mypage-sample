@extends('adminlte::page')

@section('title', '請求情報取込')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>請求情報ファイルの取込みを行います。<br>
    請求情報ファイル（csv）を1つにまとめたZipファイルを指定してください。</p>
    </p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="30%">請求書年月</th>
            <td>{{$invoice_import->invoice_date->format('Y年m月')}}分</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.file caption="請求情報ファイル" id="upload_file_kobetsu" :rules=$rules />

    <x-bs.callout>
        ファイル形式: Zip形式<br>
        各校舎の請求情報ファイル（csv）をまとめて圧縮し、1つのZipファイルを作成してください。
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="invoiceDate" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop