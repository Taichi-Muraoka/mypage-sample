@extends('adminlte::page')

@section('title','授業スケジュール登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>追加授業スケジュールの登録を行います。</p>

    <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData />

    <x-input.select caption="指導スペース" id="classroomcd" :select2=true :editData="$editData">
        <option value="1" selected>Aテーブル</option>
        <option value="2">Bテーブル</option>
        <option value="3">Cテーブル</option>
    </x-input.select>

    <x-input.date-picker caption="授業日" id="curDate" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-input.select caption="コース名" id="course_cd" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>個別指導コース</option>
        <option value="4">集団指導</option>
        <option value="5">その他・自習</option>
    </x-input.select>

    <x-input.select vShow="form.course_cd != 5" caption="講師" id="tid" :select2=true :editData="$editData">
        <option value="1">CWテスト教師１</option>
        <option value="2">CWテスト教師２</option>
    </x-input.select>

    <div v-cloak>
        <x-input.select vShow="form.course_cd != 4" caption="生徒" id="sid" :select2=true :editData="$editData">
            <option value="1">CWテスト生徒１</option>
            <option value="2">CWテスト生徒２</option>
            <option value="3">CWテスト生徒３</option>
        </x-input.select>

        <x-input.select vShow="form.course_cd == 4" caption="参加生徒選択" id="sid2" :select2=true :editData="$editData" multiple>
            <option value="1">CWテスト生徒１</option>
            <option value="2">CWテスト生徒２</option>
            <option value="3">CWテスト生徒３</option>
        </x-input.select>
    </div>

    <div v-cloak>
        <x-input.select vShow="form.course_cd != 5" caption="教科" id="subject_cd" :select2=true :select2Search=false :editData="$editData">
            <option value="1" selected>国語</option>
            <option value="2">数学</option>
            <option value="3">理科</option>
            <option value="4">社会</option>
            <option value="5">英語</option>
        </x-input.select>

        <x-input.select vShow="form.course_cd != 5" caption="通塾" id="howto" :select2=true :select2Search=false :editData="$editData">
            <option value="1" selected>両者通塾</option>
            <option value="2">生徒通塾－教師オンライン</option>
            <option value="3">生徒オンライン－教師通塾</option>
            <option value="4">両者オンライン</option>
        </x-input.select>
    </div>

    <x-input.select vShow="form.course_cd != 5" caption="出欠ステータス" id="todayabsent" :select2=true :select2Search=false :editData="$editData">
        <option value="0" selected>実施前</option>
        <option value="1">当日欠席（講師出勤なし）</option>
        <option value="2">当日欠席（講師出勤あり）</option>
        <option value="3">後日振替（振替日未定）</option>
        <option value="4">後日振替（振替日決定）</option>
        <option value="5">出席</option>
    </x-input.select>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
            <x-button.back />

            {{-- 登録時 --}}
            <x-button.submit-new />

        </div>
    </x-slot>

</x-bs.card>

@stop