@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>科目コード</th>
        <td>001</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>国語</td>
    </tr>

</x-bs.table>

@overwrite