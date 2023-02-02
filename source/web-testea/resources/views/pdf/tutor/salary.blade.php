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

<p>{{$salary->salary_date->format('Y年m月')}}度給与（{{$salary->prev_month->format('Y年m月')}}分）</p>
<p>株式会社 コー・ワークス</p>
<p>{{$salary->teacher_name}} 様</p>

<table class="border">
    <tr>
        <th width="150px">税額表</th>
        <td width="390px">{{$salary->tax_table}}</td>
    </tr>
    <tr>
        <th>扶養人数</th>
        <td>{{$salary->dependents}}</td>
    </tr>
</table>

@if (count($salary_detail_1) > 0)
<br>
<br>
支給<br>
<table class="border">
    @for ($i = 0; $i < count($salary_detail_1); $i++) <tr>
        <th width="150px">{{$salary_detail_1[$i]->item_name}}</th>
        <td width="390px" class="text-right">{{number_format($salary_detail_1[$i]->amount)}}</td>
        </tr>
        @endfor
</table>
@endif

@if (count($salary_detail_2) > 0)
<br>
<br>
控除<br>
<table class="border">
    @for ($i = 0; $i < count($salary_detail_2); $i++) <tr>
        <th width="150px">{{$salary_detail_2[$i]->item_name}}</th>
        <td width="390px" class="text-right">{{number_format($salary_detail_2[$i]->amount)}}</td>
        </tr>
        @endfor
</table>
@endif

@if (count($salary_detail_3) > 0)
<br>
<br>
その他<br>
<table class="border">
    @for ($i = 0; $i < count($salary_detail_3); $i++) <tr>
        <th width="150px">{{$salary_detail_3[$i]->item_name}}</th>
        <td width="390px" class="text-right">{{number_format($salary_detail_3[$i]->amount)}}</td>
        </tr>
        @endfor
</table>
@endif

<br>
<br>

合計<br>
<table class="border">
    @for ($i = 0; $i < count($salary_detail_4); $i++) <tr>
        <th style="font-size:16pt;" width="150px">{{$salary_detail_4[$i]->item_name}}</th>
        <td style="font-size:16pt;" width="390px" class="text-right">{{number_format($salary_detail_4[$i]->amount)}}</td>
        </tr>
        @endfor
</table>