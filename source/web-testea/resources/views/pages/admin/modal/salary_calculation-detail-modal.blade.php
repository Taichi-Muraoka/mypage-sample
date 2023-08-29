@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">年月</th>
        <td>2023年03月</td>
    </tr>
    <tr>
        <th width="35%">講師名</th>
        <td>CWテスト講師１０１</td>
    </tr>
    <tr>
        <th width="35%">授業時給(ベース給)</th>
        <td class="t-price">1,300</td>
    </tr>
    <tr>
        <th>事務作業給（一律）</th>
        <td class="t-price">1,072</td>
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
        <td class="t-price">18</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>１対２</td>
        <td class="t-price">3</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>１対３</td>
        <td class="t-price">4.5</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>集団</td>
        <td class="t-price">3</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>家庭教師</td>
        <td class="t-price">6</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>演習</td>
        <td class="t-price">2</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>ハイプラン</td>
        <td class="t-price">10</td>
        <td class="t-price"></td>
    </tr>
    <tr>
        <td>事務作業</td>
        <td class="t-price">2</td>
        <td class="t-price">2,144</td>
    </tr>
    <tr>
        <td>経費（源泉計算対象）</td>
        <td></td>
        <td class="t-price">1,500</td>
    </tr>
    <tr>
        <td>経費（源泉計算対象外）</td>
        <td></td>
        <td class="t-price">800</td>
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
        <td class="t-price">500</td>
        <td class="t-price">8</td>
        <td class="t-price">4,000</td>
    </tr>
    <tr>
        <td>交通費2</td>
        <td class="t-price">600</td>
        <td class="t-price">2</td>
        <td class="t-price">1,200</td>
    </tr>
    <tr>
        <td>交通費3</td>
        <td class="t-price">0</td>
        <td class="t-price">0</td>
        <td class="t-price">0</td>
    </tr>
</x-bs.table>

@overwrite