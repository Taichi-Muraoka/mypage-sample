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
    <tr>
        <th>曜日</th>
        <td>@{{item.day_name}}曜</td>
    </tr>
    <tr>
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
    <tr v-show="item.tutor_name">
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
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && item.subject_name">
        <th>教科</th>
        <td>@{{item.subject_name}}</td>
    </tr>
    {{-- v-showは、コース種別によって非表示の場合があるため --}}
    <tr v-show="item.how_to_kind_name && (item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }})">
        <th>通塾</th>
        <td>@{{item.how_to_kind_name}}</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@if (request()->routeIs('regular_schedule'))
{{-- 更新ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('regular_schedule-edit', '') }}/' + item.regular_class_id" icon="" caption="スケジュール編集" />
{{-- コピー登録ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('regular_schedule-copy', '') }}/' + item.regular_class_id" icon="" caption="コピー登録" />
@endif
@overwrite