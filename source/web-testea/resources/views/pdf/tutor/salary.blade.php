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

<p>{{$salary->teacher_name}} 様</p>

お世話になっております、個別指導塾TESTEAです。<br>
8月16日支給分の給与明細です。<br>
ご確認の程よろしくお願いいたします。<br>

<p>2023年7月労働分</p>
<br>

<table class="border">
    @for ($i = 0; $i < count($salary_detail_4); $i++) <tr>
        <th style="font-size:16pt;" width="150px">支払金額</th>
        <td style="font-size:16pt;" width="390px" class="text-right">89,072円</td>
        </tr>
        @endfor
</table>

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
    <tr>
        <td>授業給（個別）</td>
        <td class="text-right">1,600円</td>
        <td class="text-right">28.5</td>
        <td class="text-right">67,200円</td>
    </tr>
    <tr>
        <td>授業給（家庭教師）</td>
        <td class="text-right">3,500円</td>
        <td class="text-right">3</td>
        <td class="text-right">10,500円</td>
    </tr>
    <tr>
        <td>事務作業給</td>
        <td class="text-right">988円</td>
        <td class="text-right">2</td>
        <td class="text-right">988円</td>
    </tr>
    <tr>
        <td>特別報酬</td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right">1,800円</td>
    </tr>
    <tr>
        <td>ペナルティ</td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right">-800円</td>
    </tr>
    <tr>
        <td>小計</td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right">80,676円</td>
    </tr>
</table>
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
    <tr>
        <td>交通費</td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right">4,396円</td>
    </tr>
    <tr>
        <td>経費</td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right">4,000円</td>
    </tr>
    <tr>
        <td>小計</td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right">8,396円</td>
    </tr>
</table>

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
<br>
<table class="border">
    <tr>
        <th width="150px">備考</th>
        <td width="390px">特別報酬：1200円×1.5時間 遅刻：07/13 15分</td>
    </tr>
</table>
