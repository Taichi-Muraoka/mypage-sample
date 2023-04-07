@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>開始日</th>
        <td>2022/04/01</td>
    </tr>
    <tr>
        <th>終了日</th>
        <td>2023/03/31</td>
    </tr>
    <tr>
        <th>月額</th>
        <td>16,390</td>
    </tr>
    <tr>
        <th>契約情報</th>
        <td>月4回 60分 個別（中学1･2年生）料金</td>
    </tr>

</x-bs.table>

<x-bs.form-title>契約詳細</x-bs.form-title>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th>講師名</th>
        <th width="10%">曜日</th>
        <th width="15%">開始時刻</th>
        <th width="15%">授業時間</th>
        <th width="10%">回数</th>
        <th width="10%">教科</th>
    </x-slot>

    <tr>
        <td>CWテスト教師１０１</td>
        <td>月</td>
        <td>16:00</td>
        <td>60分</td>
        <td>4</td>
        <td>数学</td>
    </tr>
</x-bs.table>

@overwrite