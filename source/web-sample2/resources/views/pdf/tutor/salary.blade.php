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

<p style="text-align:center;font-size:20pt;text-decoration: underline;">給与明細書</p>

<p>{{$salary->tutor_name}} 様</p>

お世話になっております、個別指導塾コー・ワークスです。<br>
{{$salary_import->payment_date->format('n月j日')}}支給分の給与明細です。<br>
ご確認の程よろしくお願いいたします。

<p>{{$salary->salary_date->format('Y年n月')}}労働分</p>

<table class="border">
    <tr>
        <th style="font-size:16pt;" width="150px">支払金額</th>
        <td style="font-size:16pt;" width="390px" class="text-right">{{number_format($salary->total_amount)}}円</td>
    </tr>
</table>

@if(count($salary_detail_1) > 0)
<br>
<br>
【源泉計算対象】<br>
<table class="border">
    <tr>
        <th width="270px">費目</th>
        <th width="90px">単価</th>
        <th width="90px">時間(h)</th>
        <th width="90px">金額</th>
    </tr>

    @for ($i = 0; $i < count($salary_detail_1); $i++)
        {{-- 源泉計算用小計の金額は小計に表示する --}}
        @if($salary_detail_1[$i]->item_name != config('appconf.subtotal_withholding'))
            <tr>
                <td>{{$salary_detail_1[$i]->item_name}}</td>
                <td class="text-right">
                    @if($salary_detail_1[$i]->hour_payment != null)
                    {{number_format($salary_detail_1[$i]->hour_payment)}}円
                    @endif
                </td>
                <td class="text-right">
                    @if($salary_detail_1[$i]->hour != null)
                    {{floatval($salary_detail_1[$i]->hour)}}
                    @endif
                </td>
                <td class="text-right">{{number_format($salary_detail_1[$i]->amount)}}円</td>
            </tr>
        @else
            <tr>
                <td>小計</td>
                <td class="text-right" colspan="3">{{number_format($salary_detail_1[$i]->amount)}}円</td>
            </tr>
        @endif
    @endfor
</table>
@endif

@if(count($salary_detail_2) > 0)
<br>
<br>
【源泉計算対象外】<br>
<table class="border">
    <tr>
        <th width="270px">費目</th>
        <th width="90px"></th>
        <th width="90px"></th>
        <th width="90px">金額</th>
    </tr>
    {{-- テーブル --}}
    @for ($i = 0; $i < count($salary_detail_2); $i++)
        <tr>
            <td>{{$salary_detail_2[$i]->item_name}}</td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right">{{number_format($salary_detail_2[$i]->amount)}}円</td>
        </tr>
    @endfor
    <tr>
        <td>小計</td>
        <td class="text-right" colspan="3">{{number_format($salary_detail_2_subtotal)}}円</td>
    </tr>
</table>
@endif

@if (count($salary_detail_3) > 0)
<br>
<br>
【控除】<br>
<table class="border">
    <tr>
        <th width="270px">費目</th>
        <th width="90px"></th>
        <th width="90px"></th>
        <th width="90px">金額</th>
    </tr>
    {{-- テーブル --}}
    @for ($i = 0; $i < count($salary_detail_3); $i++)
        <tr>
            <td>{{$salary_detail_3[$i]->item_name}}</td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right">{{number_format($salary_detail_3[$i]->amount)}}円</td>
        </tr>
    @endfor
    <tr>
        <td>小計</td>
        <td class="text-right" colspan="3">{{number_format($salary_detail_3_subtotal)}}円</td>
    </tr>
</table>
@endif

<br>
<br>
<table class="border">
    <tr>
        <th width="150px">備考</th>
        <td width="390px">{!! nl2br(e($salary->memo)) !!}</td>
    </tr>
</table>
