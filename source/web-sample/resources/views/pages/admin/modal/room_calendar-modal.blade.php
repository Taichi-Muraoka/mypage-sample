@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr v-show="item.booth_name">
        <th width="35%">ブース</th>
        <td>@{{item.booth_name}}</td>
    </tr>
    <tr v-show="item.course_name">
        <th width="35%">コース名</th>
        <td>@{{item.course_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
        <th>授業区分</th>
        <td>@{{item.lesson_kind_name}} @{{item.hurikae_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.lesson_kind == {{ App\Consts\AppConst::CODE_MASTER_31_2 }}">
        <th>仮登録フラグ</th>
        <td>@{{item.tentative_name}}</td>
    </tr>
    <tr v-show="item.holiday_name">
        <th>期間区分</th>
        <td>@{{item.holiday_name}}</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>@{{$filters.formatYmdDay(item.target_date)}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.period_no && item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
        <th>時限</th>
        <td>@{{item.period_no}}限</td>
    </tr>
    <tr v-show="item.start_time">
        <th>開始時刻</th>
        <td>@{{item.start_time}}</td>
    </tr>
    <tr v-show="item.end_time">
        <th>終了時刻</th>
        <td>@{{item.end_time}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.tutor_name && (item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }})">
        <th>講師名</th>
        <td>@{{item.tutor_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.student_name && item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
        <th>生徒名</th>
        <td>@{{item.student_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.class_student_names && item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
        <th>受講生徒名</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.class_student_names}}</td>
    </tr>
    <tr v-show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && item.subject_name">
        <th>教科</th>
        <td>@{{item.subject_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.how_to_kind_name && (item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }})">
        <th>通塾</th>
        <td>@{{item.how_to_kind_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.substitute_kind_name && (item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }})">
        <th>授業代講</th>
        <td>@{{item.substitute_kind_name}}</td>
    </tr>
    <tr v-show="item.absent_tutor_name">
        <th>欠席講師名</th>
        <td>@{{item.absent_tutor_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.absent_name && (item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }})">
        <th>出欠ステータス</th>
        <td>@{{item.absent_name}}</td>
    </tr>
    {{-- v-showは、データ作成区分によって非表示の場合があるため --}}
    <tr v-show="item.transfer_date && (item.create_kind == {{ App\Consts\AppConst::CODE_MASTER_32_2 }})">
        <th>振替元授業日・時限</th>
        <td>@{{$filters.formatYmdDay(item.transfer_date)}} @{{item.transfer_period_no}}限</td>
    </tr>
    <tr v-show="item.admin_name">
        <th>登録・担当者名</th>
        <td>@{{item.admin_name}}</td>
    </tr>
    <tr v-show="!item.holiday_name">
        <th>管理者用メモ</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.memo}}</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@if (request()->routeIs('room_calendar'))
{{-- 欠席登録ボタンを表示 --}}
<x-button.edit v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}" vueHref="'{{ route('room_calendar-absent', '') }}/' + item.schedule_id" icon="" caption="欠席登録" />
{{-- 授業情報更新ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('room_calendar-edit', '') }}/' + item.schedule_id" icon="" caption="スケジュール編集" />
{{-- コピー登録ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('room_calendar-copy', '') }}/' + item.schedule_id" icon="" caption="コピー登録" />
@endif
@overwrite
