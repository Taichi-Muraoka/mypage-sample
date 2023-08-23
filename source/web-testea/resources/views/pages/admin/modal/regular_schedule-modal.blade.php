@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr v-show="item.mdType != {{ App\Consts\AppConst::EXT_GENERIC_MASTER_109_4 }}">
        <th width="35%">校舎</th>
        <td>@{{item.mdClassName}}</td>
    </tr>
    <tr>
        <th width="35%">ブース</th>
        <td>Aテーブル</td>
    </tr>
    <tr>
        <th width="35%">コース名</th>
        <td>個別指導コース</td>
    </tr>
    <tr>
        <th>曜日</th>
        <td>月曜</td>
    </tr>
    {{-- v-showは、スケジュール種別によって非表示の場合があるため --}}
    <tr>
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
    <tr>
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr v-Show="item.lesson_type != 1">
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr v-Show="item.lesson_type == 1">
        <th>受講生徒名</th>
        <td>CWテスト生徒１<br>CWテスト生徒２<br>CWテスト生徒３</td>
    </tr>
    <tr v-show="item.mdSubject">
        <th>科目</th>
        <td>@{{item.mdSubject}}</td>
    </tr>
    <tr>
        <th>通塾</th>
        <td>生徒オンライン－教師通塾</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

{{-- 更新ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('regular_schedule-edit', ['','']) }}/' + item.lesson_type + '/' + item.id" icon="" caption="スケジュール編集" />
{{-- コピー登録ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('regular_schedule-copy', ['','']) }}/' + item.lesson_type + '/' + item.id" icon="" caption="コピー登録" />

@overwrite