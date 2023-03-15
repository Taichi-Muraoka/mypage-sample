@extends('adminlte::page')

@section('title', (request()->routeIs('regular_schedule-edit')) ? 'レギュラースケジュール編集' : ((request()->routeIs('regular_schedule-new')) ? 'レギュラースケジュール登録' : 'レギュラースケジュールコピー登録'))

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'レギュラースケジュール ')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="roomcd" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />

    <p>レギュラースケジュールの{{(request()->routeIs('regular_schedule-edit')) ? '変更' : ((request()->routeIs('regular_schedule-new')) ? '登録' : 'コピー登録')}}を行います。</p>

    <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData />

    <x-input.select caption="指導スペース" id="classroomcd" :select2=true :editData="$editData">
        <option value="1" selected>Aテーブル</option>
        <option value="2">Bテーブル</option>
        <option value="3">Cテーブル</option>
    </x-input.select>

    <x-input.select caption="曜日" id="day_no" :select2=true :select2Search=false :rules=$rules :editData="$editData">
        <option value="1" selected>月</option>
        <option value="2">火</option>
        <option value="3">水</option>
        <option value="4">木</option>
        <option value="5">金</option>
        <option value="6">土</option>
    </x-input.select>

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-input.select caption="コース名" id="course_cd" :select2=true :select2Search=false :editData="$editData">
        <option value="1">個別指導コース</option>
        <option value="4">集団指導</option>
        <option value="5">その他・自習</option>
    </x-input.select>

    <x-input.select caption="講師" id="tid" :select2=true :editData="$editData">
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

    <x-input.select caption="教科" id="subject_cd" :select2=true :select2Search=false :editData="$editData">
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

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
            <x-button.back url="{{ route('regular_schedule') }}" />

            @if (request()->routeIs('regular_schedule-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop