@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">校舎</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th width="35%">ブース</th>
        <td>Aテーブル</td>
    </tr>
    <tr>
        <th width="35%">コース名</th>
        <td>@{{item.course_name}}</td>
    </tr>
    <tr v-Show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_4 }}">
        <th>授業種別</th>
        <td>通常</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>@{{$filters.formatYmd(item.target_date)}}</td>
    </tr>
    <tr>
        <th>時限</th>
        <td>@{{item.period_no}}</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>@{{item.start_time}}</td>
    </tr>
    <tr>
        <th>終了時刻</th>
        <td>@{{item.end_time}}</td>
    </tr>

    {{-- 生徒のみ表示 --}}
    @can('student')
    <tr v-Show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    @endcan

    {{-- 講師のみ表示 --}}
    {{-- 個別指導の場合 --}}
    @can('tutor')
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    {{-- 集団授業の場合 --}}
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
        <th>受講生徒名</th>
        <td>CWテスト生徒１<br>CWテスト生徒２<br>CWテスト生徒３</td>
    </tr>
    @endcan

    <tr v-show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && item.subject_name">
        <th>科目</th>
        <td>@{{item.subject_name}}</td>
    </tr>
    <tr v-Show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
        <th>通塾</th>
        <td>生徒オンライン－教師通塾</td>
    </tr>
    <tr v-Show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
        <th>授業代講</th>
        <td>なし</td>
    </tr>
    <tr v-Show="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
        <th>出欠ステータス</th>
        <td>未実施・出席</td>
    </tr>
    {{-- 管理者のみ表示 --}}
    @can('admin')
    <tr>
        <th>メモ</th>
        <td></td>
    </tr>
    @endcan

</x-bs.table>

@overwrite

@section('modal-button')

@overwrite