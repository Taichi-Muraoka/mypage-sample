@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th width="35%">授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>教室</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.sname}}</td>
    </tr>
    <tr>
        <th>授業時間数</th>
        <td>@{{item.r_minutes}}</td>
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

    {{-- 詳細の方は保護者よりコメントを入れる --}}
    <tr>
        <th>保護者よりコメント</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.parents_comment}}</td>
    </tr>
</x-bs.table>

@overwrite