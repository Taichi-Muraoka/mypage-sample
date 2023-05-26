@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>指導ブースID</th>
        <td>001</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>指導ブースコード</th>
        <td>110</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>Aテーブル</td>
    </tr>
    <tr>
        <th>表示順</th>
        <td>10</td>
    </tr>
    <tr>
        <th>cat</th>
        <td></td>
    </tr>

</x-bs.table>

@overwrite