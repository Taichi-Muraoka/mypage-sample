@extends('adminlte::page')

@section('title', '欠席申請編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')


{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下の欠席申請について変更を行います。</p>

    <x-input.date-picker caption="申請日" id="apply_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-bs.form-group name="lesson_type">
        <x-input.radio caption="個別教室" id="r_room" name="lesson_type" value="{{ App\Consts\AppConst::CODE_MASTER_8_1 }}"
            :editData=$editData />
        <x-input.radio caption="家庭教師" id="r_tutor" name="lesson_type" value="{{ App\Consts\AppConst::CODE_MASTER_8_2 }}"
            :editData=$editData />
    </x-bs.form-group>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 個別教室 --}}
    <x-bs.card vShow="form.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_1 }}">

        {{-- チェンジイベントを取得し、教室と教師を取得する --}}
        <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData
            :mastrData=$scheduleMaster />

        {{-- 詳細を表示 --}}
        <x-bs.table :hover=false :vHeader=true class="mb-4">
            <tr>
                <th width="15%">教室</th>
                <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
            </tr>
            <tr>
                <th>教師</th>
                <td><span v-cloak>@{{selectGetItem.teacher_name}}</span></td>
            </tr>
        </x-bs.table>
    </x-bs.card>

    {{-- 家庭教師 --}}
    <x-bs.card vShow="form.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_2 }}">

        <x-input.date-picker caption="授業日" id="lesson_date" :editData=$editData />

        <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

        <x-input.select caption="教師名" id="tid" :select2=true :editData=$editData :mastrData=$teacherList />

    </x-bs.card>

    <x-input.textarea caption="欠席理由" id="absent_reason" :rules=$rules :editData=$editData />

    <x-input.select id="state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$statusList />

    {{-- hidden --}}
    <x-input.hidden id="absent_apply_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop