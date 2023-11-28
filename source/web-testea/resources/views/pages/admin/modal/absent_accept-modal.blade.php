@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
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
    <tr>
        <th>ステータス</th>
        <td>@{{item.status_name}}</td>
    </tr>
</x-bs.table>

@overwrite