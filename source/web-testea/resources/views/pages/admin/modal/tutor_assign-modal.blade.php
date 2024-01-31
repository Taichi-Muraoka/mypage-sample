@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th>校舎</th>
        <td>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>@{{item.tutor_name}}</td>
    </tr>
    <tr>
        <th>講師電話番号</th>
        <td>@{{item.tel}}</td>
    </tr>
    <tr>
        <th>講師メールアドレス</th>
        <td><a v-bind:href="'mailto:' + item.email">@{{item.email}}</a></td>
    </tr>
    <tr>
        <th>性別</th>
        <td>@{{item.gender_name}}</td>
    </tr>
    <tr>
        <th>在籍大学</th>
        <td>@{{item.school_u_name}}</td>
    </tr>
    <tr>
        <th>出身高校</th>
        <td>@{{item.school_h_name}}</td>
    </tr>
    <tr>
        <th>出身中学</th>
        <td>@{{item.school_j_name}}</td>
    </tr>
    <tr>
        <th>曜日</th>
        <td>@{{item.day_name}}曜</td>
    </tr>
    <tr>
        <th>時限</th>
        <td>@{{item.period_no}}限</td>
    </tr>
    <tr>
        <th>担当科目</th>
        <td>@{{item.subject_name}}</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite
