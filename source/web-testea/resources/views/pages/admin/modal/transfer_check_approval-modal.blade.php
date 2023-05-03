@extends('pages.common.modal')

@section('modal-body')

<p>
    以下の振替調整依頼を承認します。<br>
    よろしいですか？
</p>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td>2023/01/08</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>CWテスト講師１０１</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>授業日・時限</th>
        <td>2023/01/15 6限</td>
    </tr>
    <tr>
        <th>当月依頼回数</th>
        <td>2</td>
    </tr>
</x-bs.table>

@overwrite