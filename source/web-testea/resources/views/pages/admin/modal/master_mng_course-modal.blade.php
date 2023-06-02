@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>コースコード</th>
        <td>101</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>個別指導</td>
    </tr>

</x-bs.table>

@overwrite