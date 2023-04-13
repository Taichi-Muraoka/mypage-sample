@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">年月</th>
        <td>2023年03月</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>合計金額</th>
        <td>35,390</td>
    </tr>

</x-bs.table>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th width="25%">内訳</th>
        <th width="25%">金額</th>
    </x-slot>

    <tr>
        <td>月4回 60分 個別（中学1･2年生）</td>
        <td>16,390</td>
    </tr>
    <tr>
        <td>春季特別講習（中学１・２年生 ４回）</td>
        <td>12,000</td>
    </tr>
    <tr>
        <td>追加授業（中学１・２年生 ２回）</td>
        <td>7,000</td>
    </tr>
</x-bs.table>

@overwrite