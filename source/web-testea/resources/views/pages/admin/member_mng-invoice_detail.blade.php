@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', '請求書表示')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-invoice', $editData['sid']))

@section('parent_page_title', '請求情報一覧')

@section('content')

<x-bs.card :form=true>

    <p>{{$invoice->invoice_date->format('Y年n月')}}分 お月謝のお知らせ</p>
    <p>{{$invoice->student_name}} 様 保護者様</p>
    <p>{{$invoice_import->issue_date->format('Y年n月j日')}} 発行</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">お月謝期間</th>
            <td>
                @if ($invoice_import->term_text1 != ''){{$invoice_import->term_text1}}<br>@endif
                @if ($invoice_import->term_text2 != ''){{$invoice_import->term_text2}}@endif
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
            <td>{{$invoice_detail[$i]->description}}</td>
            <td class="t-price">
                @if($invoice_detail[$i]->unit_price != null)
                {{number_format($invoice_detail[$i]->unit_price)}}円
                @endif
            </td>
            <td class="t-price">
                @if($invoice_detail[$i]->times != null)
                {{number_format($invoice_detail[$i]->times)}}
                @endif
            </td>
            <td class="t-price">{{number_format($invoice_detail[$i]->amount)}}円</td>
            </tr>
            @endfor
            <tr>
                <td class="font-weight-bold">合計</td>
                <td class="font-weight-bold t-price" colspan="3">{{number_format($invoice->total_amount)}}円</td>
            </tr>
            @endif

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">お支払方法</th>
            <td>{{$invoice->pay_type_name}}</td>
        </tr>
        <tr>
            @if ($invoice->pay_type == AppConst::CODE_MASTER_21_1)<th>お引落日</th>
            @elseif ($invoice->pay_type == AppConst::CODE_MASTER_21_2)<th>お振込期限</th>
            @endif
            <td>{{$formatter::formatYmdDayString($invoice_import->bill_date)}}</td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{!! nl2br(e($invoice->note)) !!}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <p>お月謝についてのお問い合わせは、校舎のメールアドレス（{{$invoice->email_campus}}）または、<br>
        マイページの「問い合わせ」ページへお願いいたします。</p>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back url="{{route('member_mng-invoice', $editData['sid'])}} " />
            <div class="d-flex justify-content-end">
                <x-button.submit-href caption="PDFダウンロード" icon="fas fa-download"
                    href="{{ Route('member_mng-pdf_invoice', ['sid' => $editData['sid'], 'date' => $editData['date']]) }}" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop