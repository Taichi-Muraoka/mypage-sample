@extends('pages.common.modal')

@section('modal-body')

<p>
    以下の振替調整依頼を承認します。<br>
    承認すると生徒へ振替調整依頼のお知らせの通知とメールが送信されます。<br>
    よろしいですか？
</p>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td v-cloak>@{{$filters.formatYmd(item.apply_date)}}</td>
    </tr>
    <tr>
        <th>授業日・時限</th>
        <td v-cloak>@{{$filters.formatYmdDay(item.lesson_target_date)}} @{{item.lesson_period_no}}限</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td v-cloak>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>コース</th>
        <td v-cloak>@{{item.course_name}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td v-cloak>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td v-cloak>@{{item.lesson_tutor_name}}</td>
    </tr>
    <tr>
        <th>教科</th>
        <td v-cloak>@{{item.subject_name}}</td>
    </tr>
    <tr>
        <th>振替理由／連絡事項など</th>
        <td class="nl2br" v-cloak>@{{item.transfer_reason}}</td>
    </tr>
    <tr>
        <th>当月依頼回数</th>
        <td v-cloak>@{{item.monthly_count}}</td>
    </tr>
</x-bs.table>

@overwrite