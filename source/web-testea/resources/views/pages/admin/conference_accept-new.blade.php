@extends('adminlte::page')

@section('title', '新規面談日程登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>面談スケジュールの登録を行います。</p>

    {{-- <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData /> --}}
    <x-input.select id="roomcd" caption="校舎" :select2=true >
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    <x-input.select caption="指導スペース" id="classroomcd" :select2=true :editData="$editData">
        <option value="1" selected>Aテーブル</option>
        <option value="2">Bテーブル</option>
        <option value="3">Cテーブル</option>
    </x-input.select>

    <x-input.date-picker caption="面談日" id="curDate" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.select caption="生徒" id="sid" :select2=true :editData="$editData">
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
    </x-input.select>

    <x-input.textarea id="text" caption="特記事項" :rules=$rules :editData=$editData />

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