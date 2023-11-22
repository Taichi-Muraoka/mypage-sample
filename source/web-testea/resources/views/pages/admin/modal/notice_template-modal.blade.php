@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    
    <tr>
        <th width="35%">定型文名</th>
        <td>@{{item.template_name}}</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>@{{item.title}}</td>
    </tr>
    <tr>
        <th>本文</th>
        <td class="nl2br">@{{item.text}}</td>
    </tr>
    <tr>
        <th>お知らせ種別</th>
        <td>@{{item.type_name}}</td>
    </tr>
    <tr>
        <th>表示順</th>
        <td>@{{item.order_code}}</td>
    </tr>

</x-bs.table>

@overwrite