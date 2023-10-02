@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">問い合わせ日</th>
        <td>@{{$filters.formatYmd(item.regist_time)}}</td>
    </tr>
    <tr>
        <th>宛先</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.sname}}</td>
    </tr>
    <tr>
        <th>問い合わせ件名</th>
        <td>@{{item.title}}</td>
    </tr>
    <tr>
        <th>問い合わせ内容</th>
        <td class="nl2br">@{{item.text}}</td>
    </tr>
    <tr>
        <th>回答日</th>
        <td>@{{$filters.formatYmd(item.answer_time)}}</td>
    </tr>
    <tr>
        <th>回答者所属</th>
        <td>@{{item.affiliation}}</td>
    </tr>
    <tr>
        <th>回答者名</th>
        <td>@{{item.answer_name}}</td>
    </tr>
    <tr>
        <th>回答内容</th>
        <td class="nl2br">@{{item.answer_text}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item. contact_state}}</td>
    </tr>

</x-bs.table>

@overwrite