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

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<p style="text-align:center;font-size:14pt;text-decoration: underline;">{{$invoice->invoice_date->format('Y年n月')}}分 お月謝のお知らせ</p>

<table>
    <tr>
        <td class="text-right">{{$invoice->issue_date->format('Y年n月j日')}} 発行</td>
    </tr>
</table>

<p style="font-size:12pt;">{{$invoice->sname}} 様 保護者様</p>
<br>

いつもお世話になっております、個別指導塾TESTEAです。<br>
{{$invoice->invoice_date->format('Y年n月')}}分お月謝のお知らせです。<br>
<br>

【{{$invoice->invoice_date->format('Y年n月')}}分お月謝期間】<br>
7月分お月謝期間：7月10日（月）～8月5日（土）実施分となります。<br>
※7月21日（金）より夏期特別期間となります。<br>
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
        <td>{{$invoice_detail[$i]->cost_name}}</td>
        <td class="text-right">{{number_format($invoice_detail[$i]->unit_cost)}}円</td>
        <td class="text-right">{{number_format($invoice_detail[$i]->times)}}</td>
        <td class="text-right">{{number_format($invoice_detail[$i]->cost)}}円</td>
        </tr>
    @endfor
    <tr>
        <td></td>
        <td></td>
        <td class="text-right" style="font-size:14pt;">合計</td>
        <td class="text-right" style="font-size:14pt;">{{number_format($invoice->cost_sum)}}円</td>
    </tr>
    @endif
</table>

<br>
<br>

【お支払方法】<br>
<table class="border">
    <tr>
        <th width="120px">お支払い方法</th>
        <td width="420px">{{$invoice->pay_name}}</td>
    </tr>
    @if ($invoice->billflg == 1)
    <tr>
        <th>お引落日</th>
        <td>{{$invoice->bill_date->format('Y年n月j日')}}</td>
    </tr>
    @endif
    <tr>
        <th>備考</th>
        <td>{{$invoice->note}}</td>
    </tr>
</table>
<br>
<br>
<br>

どうぞよろしくお願いいたします。<br>

