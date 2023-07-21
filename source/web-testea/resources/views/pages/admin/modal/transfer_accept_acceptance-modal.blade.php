@extends('pages.common.modal')

@section('modal-body')

<p>以下の振替連絡を受付し、以下の処理を行います。<br>
よろしいですか？</p>

<ul>
    <li>教師への受付メッセージ自動送信</li>
</ul>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">授業日・時限</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>コース</th>
        <td>個別指導コース</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>教師名</th>
        <td>@{{item.teacher_name}}</td>
    </tr>
    <tr>
        <th>振替日時</th>
        <td>@{{item.transfer_date|formatYmd}} @{{item.transfer_time|formatHm}}</td>
    </tr>
</x-bs.table>

@overwrite