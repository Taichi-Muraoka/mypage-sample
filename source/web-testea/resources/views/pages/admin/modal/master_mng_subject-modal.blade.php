@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>コード</th>
        <td>001</td>
    </tr>
    <tr>
        <th>学校区分</th>
        <td>小</td>
    </tr>
    <tr>
        <th>教科名</th>
        <td>国語</td>
    </tr>
    <tr>
        <th>表示順</th>
        <td>1</td>
    </tr>
    <tr>
        <th>状態</th>
        <td></td>
    </tr>

</x-bs.table>

@overwrite