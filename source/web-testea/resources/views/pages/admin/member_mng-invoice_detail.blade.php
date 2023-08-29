@extends('adminlte::page')

@section('title', '請求書表示')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-invoice', $editData['sid']))

@section('parent_page_title', '請求情報一覧')

@section('content')

<x-bs.card :form=true>

    <p>{{$invoice->invoice_date->format('Y年n月')}}分 お月謝のお知らせ</p>
    <p>{{$invoice->sname}} 様 保護者様</p>
    <p>{{$invoice->issue_date->format('Y年n月j日')}} 発行</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">お月謝期間</th>
            <td>
            @if ($invoice->agreement1 != ''){{$invoice->agreement1}}@endif
            @if ($invoice->agreement2 != '')<br>{{$invoice->agreement2}}@endif
            </td>
        </tr>
    </x-bs.table>

    {{-- テーブル --}}
    <x-bs.table>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="60%">摘要</th>
            <th>単価</th>
            <th>コマ数</th>
            <th>金額（税込）</th>
        </x-slot>

        @if(count($invoice_detail) > 0)
        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($invoice_detail); $i++) <tr>
            <td>{{$invoice_detail[$i]->cost_name}}</td>
            <td class="t-price">{{number_format($invoice_detail[$i]->unit_cost)}}円</td>
            <td class="t-price">{{number_format($invoice_detail[$i]->times)}}</td>
            <td class="t-price">{{number_format($invoice_detail[$i]->cost)}}円</td>
            </tr>
        @endfor
        <tr>
            <td class="font-weight-bold">合計</td>
            <td></td>
            <td></td>
            <td class="font-weight-bold t-price">{{number_format($invoice->cost_sum)}}円</td>
        </tr>
        @endif

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">お支払方法</th>
            <td>{{$invoice->pay_name}}</td>
        </tr>
        @if ($invoice->billflg == 1)
        <tr>
            <th>お引落日</th>
            <td>{{$invoice->bill_date->format('Y年n月j日')}}</td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{$invoice->note}}</td>
        </tr>
        @endif
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-4"></div>

    {{-- TODO:校舎メールアドレスは校舎マスタより取得する --}}
    <p>お月謝についてのお問い合わせは、校舎のメールアドレス（kugayama@testea.net）または、<br>
    マイページの「問い合わせ」ページへお願いいたします。</p>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back url="{{route('member_mng-invoice', $editData['sid'])}} " />
            <div class="d-flex justify-content-end">
                <x-button.submit-href caption="PDFダウンロード" icon="fas fa-download" href="{{ Route('member_mng-pdf_invoice', ['sid' => $editData['sid'], 'date' => $editData['date']]) }}"/>
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop