@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr v-show="item.mdType != {{ App\Consts\AppConst::EXT_GENERIC_MASTER_109_4 }}">
        <th width="35%">校舎</th>
        <td>@{{item.mdClassName}}</td>
    </tr>
    <tr>
        <th width="35%">指導スペース</th>
        <td>Aテーブル</td>
    </tr>
    <tr>
        <th width="35%">コース名</th>
        <td>個別指導コース</td>
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
    <tr>
        <th>教師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr v-show="item.mdSubject">
        <th>教科</th>
        <td>@{{item.mdSubject}}</td>
    </tr>
    <tr v-show="item.mdFurikae">
        <th>振替情報等</th>
        <td>@{{item.mdFurikae}}</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

{{-- 更新ボタンを表示 --}}
<x-button.edit vueHref="'{{ route('room_calendar-edit', '') }}/' + item.id" icon="" caption="スケジュール編集" />
<x-button.edit vueHref="'{{ route('room_calendar-edit', '') }}/' + item.id" icon="" caption="コピー登録" />

@overwrite