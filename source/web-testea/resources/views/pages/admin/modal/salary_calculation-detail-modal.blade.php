@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">年月</th>
        <td>2023年03月</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>CWテスト講師１０１</td>
    </tr>
    <tr>
        <th>合計金額</th>
        <td>77,000</td>
    </tr>

</x-bs.table>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th width="25%">費目</th>
        <th width="25%">時間・回数</th>
        <th width="25%">金額</th>
        <th width="25%">備考</th>
    </x-slot>

    <tr>
        <td>授業給</td>
        <td>50.5</td>
        <td>75,750</td>
        <td></td>
    </tr>
    <tr>
        <td>授業給（超過）</td>
        <td>5</td>
        <td>10,000</td>
        <td></td>
    </tr>
    <tr>
        <td>事務給</td>
        <td>10</td>
        <td>6,000</td>
        <td></td>
    </tr>
    <tr>
        <td>その他費用</td>
        <td>1</td>
        <td>1,500</td>
        <td>教材購入</td>
    </tr>
    <tr>
        <td>交通費1</td>
        <td>8</td>
        <td>4,000</td>
        <td>久我山</td>
    </tr>
    <tr>
        <td>交通費２</td>
        <td>5</td>
        <td>3,200</td>
        <td>渋谷</td>
    </tr>
</x-bs.table>

@overwrite