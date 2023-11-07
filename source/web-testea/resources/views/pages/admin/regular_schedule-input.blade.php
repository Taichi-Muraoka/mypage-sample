@extends('adminlte::page')

@section('title', (request()->routeIs('regular_schedule-edit')) ? 'レギュラースケジュール編集' : ((request()->routeIs('regular_schedule-new')) ? 'レギュラースケジュール登録' : 'レギュラースケジュールコピー登録'))

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'Default Week ')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData />
    <x-input.hidden id="course_kind" :editData=$editData />
    <x-input.hidden id="regular_class_id" :editData=$editData />
    <x-input.hidden id="kind" :editData=$editData />

    <p>レギュラースケジュールの{{(request()->routeIs('regular_schedule-edit')) ? '変更' : ((request()->routeIs('regular_schedule-new')) ? '登録' : 'コピー登録')}}を行います。</p>

    <x-bs.form-title>校舎</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData['name']}}</p>

    @if (request()->routeIs('regular_schedule-new'))
    {{-- 新規登録時 --}}
    <x-input.select id="course_cd" caption="コース" :select2=true onChange="selectChangeGetCourse" :mastrData=$courses
        :editData=$editData :select2Search=false :blank=false />
    @else
    {{-- 編集・コピー登録時 --}}
    <x-bs.form-title>コース名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData['course_name']}}</p>
    <x-input.hidden id="course_cd" :editData=$editData />
    @endif

    <x-input.select id="booth_cd" caption="ブース" :select2=true :mastrData=$booths :editData=$editData :select2Search=true
        :blank=true />

    <x-input.select id="day_cd" caption="曜日" :select2=true :mastrData=$dayList :editData=$editData :select2Search=false
        :blank=false />

    <x-input.select id="period_no" caption="時限" :select2=true :mastrData=$periods :editData=$editData onChange="selectChangeGetTimetable"
        :select2Search=false :blank=true />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-input.select vShow="form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}" caption="講師" id="tutor_id"
        :select2=true :mastrData=$tutors :editData=$editData :select2Search=true :blank=true />

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_2 }}" caption="生徒" id="student_id"
        :select2=true :mastrData=$students :editData="$editData" :select2Search=true :blank=true />

    <div v-cloak>
        <x-input.select vShow="form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}" caption="受講生徒選択"
            id="class_member_id" :select2=true :mastrData=$students :editData="$editData" :select2Search=true :blank=true
            multiple />
    </div>

    <x-input.select caption="教科" id="subject_cd" :select2=true :mastrData=$subjects :editData="$editData" :select2Search=true :blank=true />

    <x-input.select vShow="form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }} || form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}"
        caption="通塾" id="how_to_kind" :select2=true :select2Search=false :mastrData=$howToKindList :editData="$editData"
        :select2Search=false :blank=false />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 前画面に戻る --}}
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