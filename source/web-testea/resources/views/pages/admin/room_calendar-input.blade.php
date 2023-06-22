@extends('adminlte::page')

@if (isset( $editData['kind']) && $editData['kind'] == 9)
@section('title', '面談スケジュール編集')
@else
@section('title', (request()->routeIs('room_calendar-edit')) ? '授業スケジュール編集' : ((request()->routeIs('room_calendar-new')) ? '授業スケジュール登録' : '授業スケジュールコピー登録'))
@endif

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="roomcd" :editData=$editData />
    <x-input.hidden id="kind" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />

    @if (isset( $editData['kind']) && $editData['kind'] == 9)
    <p>面談スケジュールの変更を行います。</p>
    @else
    <p>授業スケジュールの{{(request()->routeIs('room_calendar-edit')) ? '変更' : ((request()->routeIs('room_calendar-new')) ? '登録' : 'コピー登録')}}を行います。</p>
    @endif

    <x-bs.form-title>校舎</x-bs.form-title>
    <p class="edit-disp-indent">久我山</p>

    <x-input.select caption="指導スペース" id="classroomcd" :select2=true :editData="$editData">
        <option value="1" selected>Aテーブル</option>
        <option value="2">Bテーブル</option>
        <option value="3">Cテーブル</option>
    </x-input.select>

    <x-input.date-picker caption="日付" id="curDate" :editData=$editData />

    @if (!isset( $editData['kind']) || (isset( $editData['kind']) && $editData['kind'] != 9))
    <x-input.select caption="時限" id="period" :select2=true :editData="$editData">
        <option value="1">1限</option>
        <option value="2">2限</option>
        <option value="3">3限</option>
        <option value="4">4限</option>
        <option value="5">5限</option>
        <option value="6">6限</option>
        <option value="7">7限</option>
    </x-input.select>
    @endif

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData />

    @if (!isset( $editData['kind']))
    <x-input.select caption="コース名" id="course_cd" :select2=true :select2Search=false :editData="$editData">
        <option value="1" selected>個別指導コース</option>
        <option value="4">集団指導</option>
        <option value="5">その他・自習</option>
    </x-input.select>

    @elseif((isset( $editData['kind']) && $editData['kind'] != 9))
    <x-bs.form-title>コース名</x-bs.form-title>
    <p class="edit-disp-indent">個別指導コース</p>
    <x-input.hidden id="course_cd" :editData=$editData value="1"/>
    @endif

    @if (!isset( $editData['kind']) || (isset( $editData['kind']) && $editData['kind'] != 9))
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
        <option value="3">振替中・未振替</option>
        <option value="4">振替済</option>
    </x-input.select>

    @endif
    <x-input.textarea id="text" caption="メモ" :rules=$rules :editData=$editData />

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