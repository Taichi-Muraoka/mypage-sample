@extends('adminlte::page')

@section('title','追加授業スケジュール登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>追加授業スケジュールの登録を行います。</p>

    {{-- スケジュール登録のバリデーションエラー時のメッセージ表示箇所 --}}
    <x-input.hidden id="booth_cd" :rules=$rules validateErr=true />
    <x-input.hidden id="student_id" :rules=$rules :editData=$editData validateErr=true />
    {{-- hidden 退避用--}}
    <x-input.hidden id="campus_cd" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$studentName}}</p>

    <x-bs.form-title>校舎</x-bs.form-title>
    <p class="edit-disp-indent">{{$campusName}}</p>

    <x-input.date-picker caption="授業日" id="target_date" :editData=$editData />

    <x-input.select id="period_no" caption="時限" :select2=true onChange="selectChangeGetTimetable"
        :editData=$editData :select2Search=false :blank=true >
        <option v-for="item in selectGetItemDate.selectItems" :value="item.code">
            @{{ item.value }}
        </option>
    </x-input.select>
    {{-- hidden 退避用--}}
    <x-input.hidden id="period_no_bef" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-input.select id="course_cd" caption="コース" :select2=true :mastrData=$courses :editData=$editData
        :select2Search=false :blank=true />

    <x-input.select id="tutor_id" caption="講師" :select2=true :rules=$rules :mastrData=$tutors
        :editData=$editData :select2Search=true :blank=true />

    <x-input.select caption="教科" id="subject_cd" :select2=true :mastrData=$subjects :editData="$editData"
        :select2Search=true :blank=true />

    <x-input.select caption="通塾" id="how_to_kind" :select2=true :select2Search=false :mastrData=$howToKindList
        :editData="$editData" :select2Search=false :blank=true />

    <x-input.textarea id="memo" caption="管理者用メモ" :rules=$rules :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 戻る --}}
            <x-button.back />

            {{-- 登録時 --}}
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop