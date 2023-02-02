@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th>付与日</th>
        <td>@{{item.grant_time|formatYmd}}</td>
    </tr>
    <tr>
        <th width="35%">申請日</th>
        <td>@{{item.apply_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>ギフトカード名</th>
        <td>@{{item.card_name}}</td>
    </tr>
    <tr>
        <th>割引内容</th>
        <td>@{{item.discount}}</td>
    </tr>
    <tr>
        <th>使用期間 開始日</th>
        <td>@{{item.term_start|formatYmd}}</td>
    </tr>
    <tr>
        <th>使用期間 終了日</th>
        <td>@{{item.term_end|formatYmd}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.status}}</td>
    </tr>
    <tr>
        <th>事務局コメント</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.comment}}</td>
    </tr>

</x-bs.table>

@overwrite