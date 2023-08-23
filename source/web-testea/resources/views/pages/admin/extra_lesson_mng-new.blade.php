@extends('adminlte::page')

@section('title','追加授業スケジュール登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>追加授業スケジュールの登録を行います。</p>

    {{-- <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData /> --}}
    <x-input.select id="roomcd" caption="校舎" :select2=true >
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    <x-input.select caption="ブース" id="classroomcd" :select2=true :editData="$editData">
        <option value="1" selected>Aテーブル</option>
        <option value="2">Bテーブル</option>
        <option value="3">Cテーブル</option>
    </x-input.select>

    <x-input.date-picker caption="授業日" id="curDate" :editData=$editData />

    <x-input.select caption="時限" id="period" :select2=true :editData="$editData">
        <option value="1">1限</option>
        <option value="2">2限</option>
        <option value="3">3限</option>
        <option value="4">4限</option>
        <option value="5">5限</option>
        <option value="6">6限</option>
        <option value="7">7限</option>
    </x-input.select>

    {{-- 時限選択時に開始時刻・終了時刻をonChangeで設定予定（時間割マスタより取得） --}}
    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-input.select caption="生徒" id="sid" :select2=true :editData="$editData">
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
    </x-input.select>

    <x-input.select caption="講師" id="tid" :select2=true :editData="$editData">
        <option value="1">CWテスト教師１</option>
        <option value="2">CWテスト教師２</option>
    </x-input.select>

    <x-input.select caption="科目" id="subject_cd" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>国語</option>
        <option value="2">数学</option>
        <option value="3">理科</option>
        <option value="4">社会</option>
        <option value="5">英語</option>
    </x-input.select>

    <x-input.select caption="通塾" id="howto" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>両者通塾</option>
        <option value="2">生徒通塾－教師オンライン</option>
        <option value="3">生徒オンライン－教師通塾</option>
        <option value="4">両者オンライン</option>
    </x-input.select>

    <x-input.textarea id="text" caption="メモ" :rules=$rules :editData=$editData />

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