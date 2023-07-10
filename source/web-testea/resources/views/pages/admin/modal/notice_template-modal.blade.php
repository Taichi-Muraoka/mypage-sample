@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    {{-- モック --}}
    <tr>
        <th width="35%">定型文名</th>
        <td>面談案内</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>面談のご案内</td>
    </tr>
    <tr>
        <th>本文</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">以下の日程で面談を実施します。
            7月1日～7月7日<br>都合の良い日をマイページからご連絡ください。</td>
    </tr>
    <tr>
        <th>お知らせ種別</th>
        <td>面談</td>
    </tr>
    <tr>
        <th>表示順</th>
        <td>1</td>
    </tr>

    {{-- 本番用 --}}
    {{-- <tr>
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
    </tr> --}}

</x-bs.table>

@overwrite