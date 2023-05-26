@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>科目コード</th>
        <td>001</td>
    </tr>
    <tr>
        <th>学校区分</th>
        <td>小</td>
    </tr>
    <tr>
        <th>仕様種別</th>
        <td>共通</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>国語</td>
    </tr>

</x-bs.table>

@overwrite