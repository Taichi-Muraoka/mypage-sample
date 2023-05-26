@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>システム変数ID</th>
        <td>101</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>事務作業時給</td>
    </tr>
    <tr>
        <th>値（数値）</th>
        <td>1000</td>
    </tr>
    <tr>
        <th>値（文字列）</th>
        <td></td>
    </tr>

</x-bs.table>

@overwrite