@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th width="35%">曜日</th>
        <td>月曜</td>
    </tr>
    <tr>
        <th width="35%">時限</th>
        <td>3限</td>
    </tr>
    <tr>
        <th>担当科目</th>
        <td>国語</td>
    </tr>
    <tr>
        <th>担当科目</th>
        <td>数学</td>
    </tr>
    <tr>
        <th>担当科目</th>
        <td>英語</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite