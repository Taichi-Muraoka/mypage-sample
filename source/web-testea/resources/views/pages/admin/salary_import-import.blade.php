@extends('adminlte::page')

@section('title', '給与情報取込')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>給与情報ファイルの取込みを行います。</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="30%">明細書年月</th>
            <td>{{$salary_import->salary_date->format('Y年m月')}}分</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="支給日" id="payment_date" :rules=$rules :editData=$editData/>

    <x-input.file caption="給与情報ファイル" id="upload_file" :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="salaryDate" :editData=$editData />

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