@extends('adminlte::page')

@section('title', '請求情報取込')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>請求情報ファイルの取込みを行います。<br>
    個別教室用・家庭教師用それぞれの請求情報ファイルを指定してください。</p>

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

    <x-input.file caption="請求情報ファイル（個別教室）" id="upload_file_kobetsu" :rules=$rules />

    <x-input.file caption="請求情報ファイル（家庭教師）" id="upload_file_katei" :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="invoiceDate" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

    <x-bs.callout>
        ファイル形式：CSV形式
    </x-bs.callout>

</x-bs.card>



@stop