@extends('adminlte::page')

@section('title', (request()->routeIs('room_calendar-edit')) ? 'スケジュール編集' : ((request()->routeIs('room_calendar-new')) ? 'スケジュール登録' : 'スケジュールコピー登録'))

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData />
    <x-input.hidden id="kind" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />

    <p>スケジュールの{{(request()->routeIs('room_calendar-edit')) ? '変更' : ((request()->routeIs('room_calendar-new')) ? '登録' : 'コピー登録')}}を行います。</p>

    <x-bs.form-title>校舎</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData['name']}}</p>

    @if (!isset( $editData['kind']))
        <x-input.select id="course_cd" caption="コース" :select2=true :mastrData=$courses :editData=$editData
            :select2Search=false :blank=false />
    @elseif(isset( $editData['kind']))
    <x-bs.form-title>コース名</x-bs.form-title>
    <p class="edit-disp-indent">個別指導コース</p>
    <x-input.hidden id="course_cd" :editData=$editData />
    @endif

    <x-input.select id="booth_cd" caption="ブース" :select2=true :mastrData=$booths :editData=$editData
        :select2Search=true :blank=true />

    <x-input.date-picker caption="日付" id="target_date" :editData=$editData />

    <x-input.select id="period_no" caption="時限" :select2=true :mastrData=$periods :editData=$editData
        :select2Search=false :blank=true />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    @if (!isset( $editData['kind']) || (isset( $editData['kind']) && $editData['kind'] != 9))
    <x-input.select vShow="form.course_cd != '90100'" caption="講師" id="tutor_id" :select2=true :mastrData=$tutors :editData=$editData
        :select2Search=true :blank=true />
    <div v-cloak>
        <x-input.select vShow="form.course_cd != '20100'" caption="生徒" id="student_id" :select2=true :mastrData=$students :editData="$editData"
            :select2Search=true :blank=true />

        <x-input.select vShow="form.course_cd == '20100'" caption="受講生徒選択" id="sid2" :select2=true :mastrData=$students :editData="$editData"
            :select2Search=true :blank=true multiple />
    </div>

    <div v-cloak>
    <x-input.select vShow="form.course_cd != 5" caption="科目" id="subject_cd"  :select2=true :mastrData=$subjects :editData="$editData"
        :select2Search=true :blank=true />

    <x-input.select vShow="form.course_cd != 5" caption="授業区分" id="status" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>通常</option>
        <option value="2">特別期間</option>
        <option value="3">初回授業</option>
        <option value="4">体験授業</option>
        <option value="5">追加</option>
    </x-input.select>

    <x-input.select vShow="form.course_cd != 5" caption="通塾" id="howto" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>両者通塾</option>
        <option value="2">生徒通塾－教師オンライン</option>
        <option value="3">生徒オンライン－教師通塾</option>
        <option value="4">両者オンライン</option>
    </x-input.select>

    <x-input.select vShow="form.course_cd != 5 && form.status == 2" caption="仮登録フラグ（特別期間講習のみ）" id="tentative_status" :select2=true :select2Search=false :editData="$editData">
        <option value="0" selected>本登録</option>
        <option value="1">仮登録</option>
    </x-input.select>


    @if (request()->routeIs('room_calendar-edit'))
    {{-- 編集時 --}}
    <x-input.select vShow="form.course_cd != 5" caption="授業代講" id="daiko" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>なし</option>
        <option value="2">代講</option>
        <option value="3">緊急代講</option>
    </x-input.select>
    </div>

    <x-input.select vShow="form.course_cd != 5 && form.daiko != 1" caption="代講講師" id="daiko_tid" :select2=true :editData="$editData">
        <option value="1">CWテスト教師１</option>
        <option value="2">CWテスト教師２</option>
    </x-input.select>

    <x-input.select vShow="form.course_cd != 5" caption="出欠ステータス" id="todayabsent" :select2=true :select2Search=false :editData="$editData">
        <option value="0" selected>実施前・出席</option>
        <option value="1">当日欠席（講師出勤なし）</option>
        <option value="2">当日欠席（講師出勤あり）</option>
        <option value="3">未振替</option>
        <option value="4">振替中</option>
        <option value="5">振替済</option>
    </x-input.select>
    @endif
    @if (request()->routeIs('room_calendar-new'))
    {{-- 登録時 --}}
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-input.checkbox caption="繰り返し登録" id="repeat_chk" name="repeat_chk" value="on" />

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.text vShow="form.repeat_chk == 'on'" id="kaisu" caption="繰り返し回数" />
    @endif

    @else
    {{-- 余白 --}}
    <x-input.select caption="生徒" id="conference_sid" :select2=true :editData="$editData">
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
    </x-input.select>
    @endif
    <x-input.textarea id="text" caption="メモ" :rules=$rules :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
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