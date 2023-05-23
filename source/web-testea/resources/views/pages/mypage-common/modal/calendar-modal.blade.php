@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr v-show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_4 }}">
        <th width="35%">校舎</th>
        <td>@{{item.mdClassName}}</td>
    </tr>
    <tr>
        <th width="35%">指導スペース</th>
        <td>Aテーブル</td>
    </tr>
    <tr>
        <th width="35%">コース名</th>
        <td>@{{item.mdTypeName}}</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>@{{item.mdDt|formatYmd}}</td>
    </tr>
    {{-- v-showは、スケジュール種別によって非表示の場合があるため --}}
    <tr v-show="item.mdStartTime">
        <th>開始時刻</th>
        <td>@{{item.mdStartTime|formatHm}}</td>
    </tr>
    <tr v-show="item.mdEndTime">
        <th>終了時刻</th>
        <td>@{{item.mdEndTime|formatHm}}</td>
    </tr>

    {{-- 生徒のみ表示 --}}
    @can('student')
    <tr v-show="item.mdTitle">
        <th>@{{item.mdTitle}}</th>
        <td>@{{item.mdTitleVal}}</td>
    </tr>
    @endcan

    {{-- 講師のみ表示 --}}
    {{-- 個別指導の場合 --}}
    @can('tutor')
    <tr v-show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_2 }}">
        <th>@{{item.mdTitle}}</th>
        <td>@{{item.mdTitleVal}}</td>
    </tr>
    {{-- 集団授業の場合 --}}
    <tr v-show="item.mdType == {{ App\Consts\AppConst::CODE_MASTER_21_2 }}">
        <th>参加生徒名</th>
        <td>CWテスト生徒１<br>CWテスト生徒２<br>CWテスト生徒３</td>
    </tr>
    @endcan

    <tr v-show="item.mdSubject">
        <th>教科</th>
        <td>@{{item.mdSubject}}</td>
    </tr>

    {{-- 講師のみ表示 --}}
    @can('tutor')
    <tr v-Show="item.lesson_type != 3">
        <th>授業種別</th>
        <td>追加</td>
    </tr>
    @endcan

    <tr v-Show="item.lesson_type != 3">
        <th>通塾</th>
        <td>生徒オンライン－教師通塾</td>
    </tr>

    {{-- 講師のみ表示 --}}
    @can('tutor')
    <tr v-Show="item.lesson_type != 3">
        <th>授業代講</th>
        <td>なし</td>
    </tr>
    @endcan

    <tr v-Show="item.lesson_type != 3">
        <th>出欠ステータス</th>
        <td>当日欠席（講師出勤あり）</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

{{-- 生徒のみ --}}
@can('student')
{{-- 欠席申請へ(IDを指定して遷移) 授業のみ --}}
<x-button.edit vueHref="'{{ route('absent') }}/' + item.id" icon="" caption="欠席申請" vShow="item.mdBtn" />
@endcan

@overwrite