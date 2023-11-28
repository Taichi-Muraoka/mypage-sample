@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    <tr>
        <th>志望順</th>
        <td>@{{item.priority_no}}</td>
    </tr>
    <tr>
        <th>受験校</th>
        <td>@{{item.school_name}}</td>
    </tr>
    <tr>
        <th>学部・学科名</th>
        <td>@{{item.department_name}}</td>
    </tr>
    <tr>
        <th>受験年度</th>
        <td>@{{item.exam_year}}</td>
    </tr>
    <tr>
        <th>受験日程名</th>
        <td>@{{item.exam_name}}</td>
    </tr>
    <tr>
        <th>受験日</th>
        <td>@{{$filters.formatYmdDay(item.exam_date)}}</td>
    </tr>
    <tr>
        <th>合否</th>
        <td>@{{item.result_name}}</td>
    </tr>
    <tr>
        <th>備考</th>
        <td class="nl2br">@{{item.memo}}</td>
    </tr>

</x-bs.table>

@overwrite