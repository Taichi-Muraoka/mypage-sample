@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th width="35%">ブース</th>
        <td>@{{item.booth_name}}</td>
    </tr>
    <tr>
        <th width="35%">コース</th>
        <td>@{{item.course_name}}</td>
    </tr>
    <tr>
        <th>授業区分</th>
        <td>@{{item.lesson_kind_name}}</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>@{{$filters.formatYmd(item.target_date)}}</td>
    </tr>
    <tr>
        <th>時限</th>
        <td>
            <span v-if="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
                @{{(item.period_no)}}限
            </span>
        </td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>@{{$filters.formatHm(item.start_time)}}</td>
    </tr>
    <tr>
        <th>終了時刻</th>
        <td>@{{$filters.formatHm(item.end_time)}}</td>
    </tr>
    <tr>
        <th>講師名/担当者名</th>
        <td>@{{item.tutor_name}}</td>
    </tr>
    {{-- 個別指導の場合 --}}
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
        <th>生徒名</th>
        <td v-cloak>@{{item.student_name}}</td>
    </tr>
    {{-- 集団授業の場合 --}}
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
        <th>受講生徒名</th>
        <td><span v-for="member in item.class_member_names" v-cloak>@{{member}}<br></span></td>
    </tr>
    <tr>
        <th>科目</th>
        <td>@{{item.subject_name}}</td>
    </tr>
    <tr>
        <th>通塾</th>
        <td>@{{item.how_to_kind_name}}</td>
    </tr>
    <tr>
        <th>授業代講</th>
        <td>@{{item.substitute_kind_name}}</td>
    </tr>
    <tr>
        <th>出欠ステータス</th>
        <td>
            <span v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">@{{item.absent_status_name}}</span>
        </td>
    </tr>
    <tr>
        <th>授業報告書ステータス</th>
        <td>@{{item.report_status}}</td>
    </tr>
    <tr v-show="item.transfer_class_id != null">
        <th>振替元授業日・時限</th>
        <td>@{{$filters.formatYmd(item.transfer_target_date)}} @{{item.transfer_priod_no}}限</td>
    </tr>
    <tr>
        <th>メモ</th>
        <td>@{{item.memo}}</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite