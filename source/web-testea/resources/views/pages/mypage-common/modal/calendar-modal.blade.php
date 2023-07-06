@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }}">
        <th width="35%">スケジュール種別</th>
        <td>授業・自習</td>
    </tr>
    <tr v-Show="item.mdType == {{ App\Consts\AppConst::CODE_MASTER_21_6 }}">
        <th width="35%">スケジュール種別</th>
        <td>面談</td>
    </tr>
    <tr>
        <th width="35%">校舎</th>
        <td>@{{item.mdClassName}}</td>
    </tr>
    <tr>
        <th width="35%">指導スペース</th>
        <td>Aテーブル</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type == 0">
        <th width="35%">コース名</th>
        <td>個別指導コース</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type == 1">
        <th width="35%">コース名</th>
        <td>集団授業</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type == 3">
        <th width="35%">コース名</th>
        <td>自習・その他</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type != 3">
        <th>授業種別</th>
        <td>通常</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>@{{item.mdDt|formatYmd}}</td>
    </tr>
    {{-- v-showは、スケジュール種別によって非表示の場合があるため --}}
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }}">
            <th>時限</th>
        <td>5時限</td>
    </tr>
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
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type != 3">
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    @endcan

    {{-- 講師のみ表示 --}}
    {{-- 個別指導の場合 --}}
    @can('tutor')
    <tr v-show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_2 }}">
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    {{-- 集団授業の場合 --}}
    <tr v-show="item.mdType == {{ App\Consts\AppConst::CODE_MASTER_21_2 }}">
        <th>受講生徒名</th>
        <td>CWテスト生徒１<br>CWテスト生徒２<br>CWテスト生徒３</td>
    </tr>
    @endcan

    <tr v-show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.mdSubject">
        <th>教科</th>
        <td>@{{item.mdSubject}}</td>
    </tr>

    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type != 3">
        <th>通塾</th>
        <td>生徒オンライン－教師通塾</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type != 3">
        <th>授業代講</th>
        <td>なし</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }} && item.lesson_type != 3">
        <th>出欠ステータス</th>
        <td>未実施・出席</td>
    </tr>
    <tr v-Show="item.mdType != {{ App\Consts\AppConst::CODE_MASTER_21_6 }}">
        <th>メモ</th>
        <td></td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite