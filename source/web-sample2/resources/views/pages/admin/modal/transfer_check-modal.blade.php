@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>申請日</th>
        <td v-cloak>@{{$filters.formatYmd(item.apply_date)}}</td>
    </tr>
    <tr>
        <th>申請者種別</th>
        <td v-cloak>@{{item.apply_kind_name}}</td>
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
        <th>振替候補日・時限１</th>
        <td v-cloak>@{{$filters.formatYmdDay(item.transfer_date_1)}} @{{item.period_no_1}}限</td>
    </tr>
    <tr>
        <th>振替候補日・時限２</th>
        <td v-cloak>
            <span v-if="item.transfer_date_2 != null">@{{$filters.formatYmdDay(item.transfer_date_2)}}
                @{{item.period_no_2}}限</span>
        </td>
    </tr>
    <tr>
        <th>振替候補日・時限３</th>
        <td v-cloak>
            <span v-if="item.transfer_date_3 != null">@{{$filters.formatYmdDay(item.transfer_date_3)}}
                @{{item.period_no_3}}限</span>
        </td>
    </tr>
    <tr>
        <th>振替理由／連絡事項など</th>
        <td class="nl2br" v-cloak>@{{item.transfer_reason}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td v-cloak>@{{item.approval_status_name}}</td>
    </tr>
    <tr v-show="item.comment != null">
        <th>承認者コメント</th>
        <td class="nl2br" v-cloak>@{{item.comment}}</td>
    </tr>
    <tr>
        <th>振替代講区分</th>
        <td v-cloak>@{{item.transfer_kind_name}}</td>
    </tr>
    {{-- 承認ステータス=管理者対応済み and 振替代講区分=代講 ではない場合に表示 --}}
    <tr v-show="!(item.approval_status == {{ App\Consts\AppConst::CODE_MASTER_3_5 }} &&
                  item.transfer_kind == {{ App\Consts\AppConst::CODE_MASTER_54_2 }})">
        <th>振替日・時限（確定）</th>
        <td v-cloak>
            <span v-if="item.transfer_schedule_id != null">@{{$filters.formatYmdDay(item.transfer_target_date)}}
                @{{item.transfer_period_no}}限</span>
        </td>
    </tr>
    <tr v-show="item.substitute_tutor_id != null">
        <th>代講講師名</th>
        <td v-cloak>@{{item.sub_tutor_name}}</td>
    </tr>

</x-bs.table>

@overwrite
