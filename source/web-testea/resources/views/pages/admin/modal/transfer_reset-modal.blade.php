@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">年度末年月</th>
        <td>2023年02月</td>
    </tr>
    <tr>
        <th>処理日</th>
        <td>2023/01/30</td>
    </tr>
    <tr>
        <th>件数</th>
        <td>100</td>
    </tr>

</x-bs.table>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true>

    <x-slot name="thead">
        <th width="25%">校舎</th>
        <th width="25%">授業日</th>
        <th width="25%">生徒名</th>
        <th width="25%">講師名</th>
    </x-slot>

    <tr>
        <td>久我山</td>
        <td>2023/01/30</td>
        <td>CWテスト生徒１</td>
        <td>CWテスト講師１０１</td>
    </tr>
</x-bs.table>

@overwrite