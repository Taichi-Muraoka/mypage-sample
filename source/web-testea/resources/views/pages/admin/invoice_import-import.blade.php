@extends('adminlte::page')

@section('title', '請求情報取込')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>請求情報ファイルの取込みを行います。<br>
    請求情報ファイル（csv）を圧縮したZipファイルを指定してください。</p>
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

    <x-bs.form-title>請求書情報</x-bs.form-title>

    <x-input.date-picker caption="請求書発行日" id="issue_date" :editData=$editData />

    <x-input.date-picker caption="月謝期間 開始日" id="start_date" :editData=$editData />

    <x-input.date-picker caption="月謝期間 終了日" id="end_date" :editData=$editData />

    <x-input.text caption="月謝期間追記1" id="term_text1" :rules=$rules :editData=$editData/>

    <x-input.text caption="月謝期間追記2" id="term_text2" :rules=$rules :editData=$editData/>

    <x-input.date-picker caption="口座引落日・振込期限日" id="bill_date" :editData=$editData />

    <x-input.file caption="請求情報ファイル" id="upload_file_kobetsu" :rules=$rules />

    <x-bs.callout>
        ファイル形式: Zip形式<br>
        請求情報ファイル（csv）を圧縮し、Zipファイルを作成してください。
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