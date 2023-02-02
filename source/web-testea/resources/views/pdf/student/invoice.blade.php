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

<p style="text-align:center;font-size:20pt;text-decoration: underline;">授業料請求書</p>

<table>
    <tr>
        <td>{{$invoice->invoice_date->format('Y年n月')}}分 請求書</td>
        <td class="text-right">{{$invoice->issue_date->format('Y年n月j日')}} 発行</td>
    </tr>
</table>

<p style="font-size:16pt;">{{$invoice->sname}} 様</p>

下記のとおり御請求申し上げます。<br>
<span style="font-size:5pt;"><br></span>{{-- 行間を微調整している --}}
<span style="text-decoration: underline;">合計金額&nbsp;&nbsp;&nbsp;¥{{number_format($invoice->cost_sum)}}(税込)</span><br>
<br>

<table class="border">
    <tr>
        <th width="150px">お支払い方法</th>
        <td width="390px">{{$invoice->pay_name}}</td>
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
</table>

<br>
<br>

<table class="border">
    <tr>
        <th width="180px">費用内容</th>
        <th width="360px">費用（税込）</th>
    </tr>
    @if(count($invoice_detail) > 0)
    {{-- テーブル行 --}}
    @for ($i = 0; $i < count($invoice_detail); $i++) <tr>
        <td>{{$invoice_detail[$i]->cost_name}}</td>
        <td class="text-right">{{number_format($invoice_detail[$i]->cost)}}</td>
        </tr>
    @endfor
    <tr>
        <td class="text-right" style="font-size:16pt;">合計</td>
        <td class="text-right" style="font-size:16pt;">{{number_format($invoice->cost_sum)}}</td>
    </tr>
    @endif
</table>

<br>
<br>

<table class="border">
    <tr>
        <th width="150px">備考</th>
        <td width="390px">{{$invoice->note}}</td>
    </tr>
</table>