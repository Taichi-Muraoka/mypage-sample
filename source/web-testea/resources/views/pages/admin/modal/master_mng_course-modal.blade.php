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
        <td>個別指導 中学生コース（受験準備学年）</td>
    </tr>

</x-bs.table>

@overwrite