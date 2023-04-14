@extends('adminlte::page')

@section('title', '特別期間講習 個別スケジュール登録')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="roomcd" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />

    <p>特別期間講習の個別スケジュール登録を行います。自動コマ割りと同様に仮スケジュールとして登録されます。</p>

    <x-input.select caption="校舎" id="roomcd" :select2=true :editData="$editData">
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">本郷</option>
    </x-input.select>

    <x-input.select caption="指導スペース" id="classroomcd" :select2=true :editData="$editData">
        <option value="1" selected>Aテーブル</option>
        <option value="2">Bテーブル</option>
        <option value="3">Cテーブル</option>
    </x-input.select>

    <x-input.date-picker caption="授業日" id="curDate" :editData=$editData />

    <x-input.select caption="時限" id="period" :select2=true :editData="$editData">
        <option value="1" selected>1時限</option>
        <option value="2">2時限</option>
        <option value="3">3時限</option>
        <option value="4">4時限</option>
        <option value="5">5時限</option>
        <option value="6">6時限</option>
        <option value="7">7時限</option>
    </x-input.select>

    <div>
        <x-input.select caption="生徒" id="sid" :select2=true :editData="$editData">
            <option value="1">CWテスト生徒１</option>
            <option value="2">CWテスト生徒２</option>
            <option value="3">CWテスト生徒３</option>
        </x-input.select>
    </div>

    <x-input.select caption="講師" id="tid" :select2=true :editData="$editData">
        <option value="1">CWテスト教師１</option>
        <option value="2">CWテスト教師２</option>
    </x-input.select>

    <x-input.select caption="教科" id="subject_cd" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>国語</option>
        <option value="2">数学</option>
        <option value="3">理科</option>
        <option value="4">社会</option>
        <option value="5">英語</option>
    </x-input.select>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop