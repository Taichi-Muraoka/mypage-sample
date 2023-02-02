@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">メールアドレス</th>
        <td>@{{item.email}}</td>
    </tr>
    <tr>
        <th>氏名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>管理教室</th>
        <td>@{{item.room_name}}</td>
    </tr>

</x-bs.table>

@overwrite