@extends('adminlte::page')

@section('title', (request()->routeIs('room_calendar-edit')) ? 'スケジュール編集' : ((request()->routeIs('room_calendar-new')) ?
'スケジュール登録' : 'スケジュールコピー登録'))

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData />
    <x-input.hidden id="course_kind" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />
    <x-input.hidden id="kind" :editData=$editData />

    <p>スケジュールの{{(request()->routeIs('room_calendar-edit')) ? '変更' : ((request()->routeIs('room_calendar-new')) ? '登録' :
        'コピー登録')}}を行います。</p>

    <x-bs.form-title>校舎</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData['name']}}</p>

    @if (request()->routeIs('room_calendar-new'))
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

    <x-input.date-picker caption="日付" id="target_date" :editData=$editData />

        {{-- hidden 退避用--}}
    <x-input.hidden id="period_no_bef" :editData=$editData />

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}" id="period_no" caption="時限" :select2=true
        onChange="selectChangeGetTimetable" :select2Search=false :blank=true>
        <option v-for="item in selectGetItemDate.selectItems" :value="item.code">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_4 }}" caption="講師" id="tutor_id"
        :select2=true :mastrData=$tutors :editData=$editData :select2Search=true :blank=true />

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_2 }}" caption="生徒" id="student_id"
        :select2=true :mastrData=$students :editData="$editData" :select2Search=true :blank=true />

    <div v-cloak>
        <x-input.select vShow="form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}" caption="受講生徒選択"
            id="class_member_id" :select2=true :mastrData=$students :editData="$editData" :select2Search=true :blank=true
            multiple />
    </div>

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}"
        caption="科目" id="subject_cd" :select2=true :mastrData=$subjects :editData="$editData" :select2Search=true :blank=true />

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_4 }}"
        caption="授業区分" id="lesson_kind" :select2=true :mastrData=$lessonKindList :editData="$editData" :select2Search=false :blank=false />

    <div v-cloak>
        <x-input.select vShow="form.lesson_kind == {{ App\Consts\AppConst::CODE_MASTER_31_2 }}"
            caption="仮登録フラグ（特別期間講習のみ）" id="tentative_status" :mastrData=$tentativeStatusList :select2=true
            :editData="$editData" :select2Search=false :blank=false />
    </div>

    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_4 }}"
        caption="通塾" id="how_to_kind" :select2=true :select2Search=false :mastrData=$howToKindList :editData="$editData"
        :select2Search=false :blank=false />

    @if (request()->routeIs('room_calendar-edit'))
    {{-- 編集時 --}}
    <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_4 }}"
        caption="授業代講" id="substitute_kind" :select2=true :mastrData=$substituteKindList :editData="$editData"
        :select2Search=false :blank=false />
    <x-input.hidden id="substitute_kind_bef" :editData=$editData />

        @if ($editData['absent_tutor_id'])
        {{-- 欠席講師が設定されている場合 --}}
        <x-bs.form-title>欠席講師</x-bs.form-title>
        <p class="edit-disp-indent">{{$editData['tutor_name']}}</p>
        <x-input.hidden id="absent_tutor_id" :editData=$editData />
        @else
        {{-- 欠席講師が設定されていない場合 --}}
        <x-input.select
        vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && form.substitute_kind != {{ App\Consts\AppConst::CODE_MASTER_34_0 }}"
        caption="代講講師" id="substitute_tid" :select2=true :mastrData=$tutors :editData="$editData" :select2Search=true
        :blank=true />
        @endif

    <x-input.select vShow="form.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}" caption="出欠ステータス"
        id="absent_status" :select2=true :mastrData=$todayabsentList :editData="$editData" :select2Search=false
        :blank=false />
    @endif

    <x-input.textarea id="memo" caption="メモ" :rules=$rules :editData=$editData />

    @if (request()->routeIs('room_calendar-new'))
    {{-- 登録時 --}}
    {{-- 余白 --}}
    <div class="mb-4"></div>
    <x-input.checkbox vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}" caption="繰り返し登録" id="repeat_chk" name="repeat_chk" value="true" />

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <div v-cloak>
        <x-input.select vShow="form.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }} && form.repeat_chk == 'true'" caption="繰り返し回数" id="repeat_times"
            :select2=true :mastrData=$times :editData=$editData :select2Search=false :blank=false />
    </div>
    @endif

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 前画面に戻る --}}
            <x-button.back url="{{ route('room_calendar') }}" />

            @if (request()->routeIs('room_calendar-edit'))
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