@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">申請日</th>
        <td>@{{item.apply_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>教師名</th>
        <td>@{{item.teacher_name}}</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>教室</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>振替日時</th>
        <td>@{{item.transfer_date|formatYmd}} @{{item.transfer_time|formatHm}}</td>
    </tr>
    <tr>
        <th>振替理由</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.transfer_reason}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.state}}</td>
    </tr>

</x-bs.table>

@overwrite