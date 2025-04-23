@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">登録日</th>
        <td>@{{$filters.formatYmd(item.regist_date)}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.sname}}</td>
    </tr>
    <tr>
        <th>サンプル件名</th>
        <td>@{{item.sample_title}}</td>
    </tr>
    <tr>
        <th>サンプルテキスト</th>
        <td class="nl2br">@{{item.sample_text}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.sample_state_name}}</td>
    </tr>
    <tr>
        <th>登録者</th>
        <td>@{{item.adm_name}}</td>
    </tr>
</x-bs.table>

@overwrite
