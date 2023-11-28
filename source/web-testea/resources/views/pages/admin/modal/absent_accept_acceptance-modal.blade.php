@extends('pages.common.modal')

@section('modal-body')

<p>以下の欠席申請を受付し、以下の処理を行います。<br>
    よろしいですか？</p>

<ul>
    <li>対象授業・対象生徒の出欠ステータスを「欠席」に変更</li>
    <li>生徒へのお知らせ自動送信</li>
    <li>生徒へのメール通知</li>
    <li>担当講師へのお知らせ自動送信</li>
    <li>担当講師へのメール通知</li>
</ul>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td>@{{$filters.formatYmd(item.apply_date)}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>授業日・時限</th>
        <td>@{{$filters.formatYmdDay(item.target_date)}} @{{item.period_no}}限</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>コース名</th>
        <td>@{{item.course_name}}</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>@{{item.tutor_name}}</td>
    </tr>
    <tr>
        <th>教科</th>
        <td>@{{item.subject_name}}</td>
    </tr>
    <tr>
        <th>欠席理由</th>
        <td class="nl2br">@{{item.absent_reason}}</td>
    </tr>
</x-bs.table>

@overwrite