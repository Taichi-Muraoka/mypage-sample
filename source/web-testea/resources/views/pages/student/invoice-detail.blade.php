@extends('adminlte::page')

@section('title', '請求書表示')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card :form=true>

    <p>{{$invoice->invoice_date->format('Y年n月')}}分 請求書</p>
    <p>{{$invoice->sname}} 様</p>
    <p>{{$invoice->issue_date->format('Y年n月j日')}} 発行</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">お支払い方法</th>
            <td>{{$invoice->pay_name}}</td>
        </tr>
        @if ($invoice->billflg == 1)
        <tr>
            <th>お引落日</th>
            <td>{{$invoice->bill_date->format('Y年n月j日')}}</td>
        </tr>
        @endif
        @if ($invoice->agreement1 != '')
        <tr>
            <th>契約内容（個別教室）</th>
            <td>{{$invoice->agreement1}}</td>
        </tr>
        @endif
        @if ($invoice->agreement2 != '')
        <tr>
            <th>契約内容（家庭教師）</th>
            <td>{{$invoice->agreement2}}</td>
        </tr>
        @endif

    </x-bs.table>

    {{-- テーブル --}}
    <x-bs.table>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="70%">費用内容</th>
            <th>費用（税込）</th>
        </x-slot>

        @if(count($invoice_detail) > 0)
        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($invoice_detail); $i++) <tr>
            <td>{{$invoice_detail[$i]->cost_name}}</td>
            <td class="t-price">{{number_format($invoice_detail[$i]->cost)}}</td>
            </tr>
        @endfor
        <tr>
            <td class="font-weight-bold">合計</td>
            <td class="font-weight-bold t-price">{{number_format($invoice->cost_sum)}}</td>
        </tr>
        @endif

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">備考</th>
            <td>{{$invoice->note}}</td>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <div class="d-flex justify-content-end">
                <x-button.submit-href caption="PDFダウンロード" icon="fas fa-download" href="{{ Route('invoice-pdf', $editData['date']) }}"/>
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop