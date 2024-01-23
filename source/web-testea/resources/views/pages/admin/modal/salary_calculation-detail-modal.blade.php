@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">年月</th>
        <td>@{{$filters.formatYmString(item.salary_date)}}</td>
    </tr>
    <tr>
        <th width="35%">講師名</th>
        <td>@{{item.tutor_name}}</td>
    </tr>
    <tr>
        <th width="35%">授業時給(ベース給)</th>
        <td class="t-price">@{{$filters.toLocaleString(item.hourly_base_wage)}}</td>
    </tr>
    <tr>
        <th>事務作業給（一律）</th>
        <td class="t-price">@{{$filters.toLocaleString(item.hour_payment)}}</td>
    </tr>

</x-bs.table>

{{-- 余白 --}}
<div class="mb-3"></div>

<x-bs.form-title>授業時間・経費</x-bs.form-title>

    {{-- ページネータなし --}}
<x-bs.table :hover=false :smartPhoneModal=true>

    <x-slot name="thead">
        <th width="35%">費目</th>
        <th width="30%">時間</th>
        <th>金額</th>
    </x-slot>

    <tr>
        <td>個別</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_personal)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>１対２</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_two)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>１対３</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_three)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>集団</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_group)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>家庭教師</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_home)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>演習</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_practice)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>ハイプラン</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_high)}}</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>事務作業</td>
        <td class="t-price">@{{$filters.numberRound(item.hour_work)}}</td>
        <td class="t-price">@{{$filters.toLocaleString(item.hour_work * item.hour_payment)}}</td>
    </tr>
    <tr>
        <td>経費（源泉計算対象）</td>
        <td></td>
        <td class="t-price">@{{$filters.toLocaleString(item.cost)}}</td>
    </tr>
    <tr>
        <td>経費（源泉計算対象外）</td>
        <td></td>
        <td class="t-price">@{{$filters.toLocaleString(item.untaxed_cost)}}</td>
    </tr>
</x-bs.table>

{{-- 余白 --}}
<div class="mb-3"></div>

<x-bs.form-title>交通費</x-bs.form-title>

<x-bs.table :hover=false :smartPhoneModal=true>

    <x-slot name="thead">
        <th width="35%">費目</th>
        <th width="20%">単価</th>
        <th width="20%">回数</th>
        <th>金額</th>
    </x-slot>
    <tr>
        <td>交通費1</td>
        <td class="t-price">@{{$filters.toLocaleString(item.unit_price1)}}</td>
        <td class="t-price">@{{item.times1}}</td>
        <td class="t-price">@{{$filters.toLocaleString(item.amount1)}}</td>
    </tr>
    <tr>
        <td>交通費2</td>
        <td class="t-price">@{{$filters.toLocaleString(item.unit_price2)}}</td>
        <td class="t-price">@{{item.times2}}</td>
        <td class="t-price">@{{$filters.toLocaleString(item.amount2)}}</td>
    </tr>
    <tr>
        <td>交通費3</td>
        <td class="t-price">@{{$filters.toLocaleString(item.unit_price3)}}</td>
        <td class="t-price">@{{item.times3}}</td>
        <td class="t-price">@{{$filters.toLocaleString(item.amount3)}}</td>
    </tr>
</x-bs.table>

@overwrite