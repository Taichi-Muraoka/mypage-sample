@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>コード</th>
        <td>110</td>
    </tr>
    <tr>
        <th>校舎名称</th>
        <td>久我山校</td>
    </tr>
    <tr>
        <th>校舎表示名称</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>校舎略称</th>
        <td>久</td>
    </tr>
    <tr>
        <th>表示順</th>
        <td>20</td>
    </tr>
    <tr>
        <th>状態</th>
        <td></td>
    </tr>

</x-bs.table>

@overwrite