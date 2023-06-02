@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>単元ID</th>
        <td>001</td>
    </tr>
    <tr>
        <th>学年</th>
        <td>小1</td>
    </tr>
    <tr>
        <th>科目</th>
        <td>国語</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>ひらがな</td>
    </tr>

</x-bs.table>

@overwrite