@inject('formatter','App\Libs\CommonDateFormat')

<style type="text/css">
    table.border {
        border: 1px solid #030303;
    }

    table.border td {
        border: 1px solid #030303;
    }

    table.border th {
        background-color: #b9b9b9;
        border: 1px solid #030303;
    }

    .text-right {
        text-align: right;
    }
</style>

<p style="text-align:center;font-size:14pt;text-decoration: underline;">{{$invoice->invoice_date->format('Y年n月')}}分
    お月謝のお知らせ</p>

<table>
    <tr>
        <td class="text-right">{{$invoice_import->issue_date->format('Y年n月j日')}} 発行</td>
    </tr>
</table>

<p style="font-size:12pt;">{{$invoice->student_name}} 様 保護者様</p>
<br>

いつもお世話になっております、個別指導塾コー・ワークスです。<br>
{{$invoice->invoice_date->format('Y年n月')}}分お月謝のお知らせです。<br>
<br>

@if ($invoice_import->term_text1 != ''){{$invoice_import->term_text1}}<br>@endif
@if ($invoice_import->term_text2 != ''){{$invoice_import->term_text2}}<br>@endif
<br>

【明細】<br>
<table class="border">
    <tr>
        <th width="270px">摘要</th>
        <th width="90px">単価</th>
        <th width="90px">コマ数</th>
        <th width="90px">金額（税込）</th>
    </tr>
    @if(count($invoice_detail) > 0)
    {{-- テーブル行 --}}
    @for ($i = 0; $i < count($invoice_detail); $i++) <tr>
        <td>{{$invoice_detail[$i]->description}}</td>
        <td class="text-right">
            @if($invoice_detail[$i]->unit_price != null)
            {{number_format($invoice_detail[$i]->unit_price)}}円
            @endif
        </td>
        <td class="text-right">
            @if($invoice_detail[$i]->times != null)
            {{number_format($invoice_detail[$i]->times)}}
            @endif
        </td>
        <td class="text-right">{{number_format($invoice_detail[$i]->amount)}}円</td>
        </tr>
        @endfor
        <tr>
            <td></td>
            <td></td>
            <td class="text-right" style="font-size:14pt;">合計</td>
            <td class="text-right" style="font-size:14pt;">{{number_format($invoice->total_amount)}}円</td>
        </tr>
        @endif
</table>

<br>
<br>

【お支払方法】<br>
<table class="border">
    <tr>
        <th width="120px">お支払い方法</th>
        <td width="420px">{{$invoice->pay_type_name}}</td>
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
</table>
<br>
<br>
<br>
お月謝についてのお問い合わせは、校舎のメールアドレス（{{$invoice->email_campus}}）
または、マイページの「問い合わせ」ページへお願いいたします。<br>
<br>
どうぞよろしくお願いいたします。<br>
