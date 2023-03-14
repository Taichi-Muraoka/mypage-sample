@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th width="35%">授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>時限</th>
        <td></td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.sname}}</td>
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
        <th>教師よりコメント</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.teacher_comment}}</td>
    </tr>
    <tr>
        <th>承認ステータス</th>
        <td class="nl2br"></td>
    </tr>
    <tr>
        <th>事務局コメント</th>
        <td class="nl2br"></td>
    </tr>
</x-bs.table>

@overwrite