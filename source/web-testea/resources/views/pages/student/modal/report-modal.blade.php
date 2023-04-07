@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th width="35%">授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>コース</th>
        <td></td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>@{{item.tname}}</td>
    </tr>
    <tr>
        <th>学習内容</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.content}}</td>
    </tr>
    <tr>
        <th>次回までの宿題</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.homework}}</td>
    </tr>
    <tr>
        <th>講師よりコメント</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.teacher_comment}}</td>
    </tr>
</x-bs.table>

@overwrite