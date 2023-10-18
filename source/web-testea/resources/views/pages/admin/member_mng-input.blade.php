@extends('adminlte::page')

@section('title', (request()->routeIs('member_mng-edit')) ? '会員情報編集' : '会員情報登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 編集画面の場合のみ、生徒カルテを経由する --}}
@if (request()->routeIs('member_mng-edit'))
@section('parent_page', route('member_mng-detail', 1))
@section('parent_page_title', '生徒カルテ')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('member_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の会員について、編集を行います。</p>
    <x-bs.form-title>生徒ID</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData['student_id']}}</p>

    @else
    {{-- 登録時 --}}
    <p>会員の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="生徒名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒名かな" id="name_kana" :rules=$rules :editData=$editData/>

    <x-bs.form-title>所属校舎</x-bs.form-title>
    <x-bs.form-group name="rooms">
        {{-- 所属校舎チェックボックス --}}
        @for ($i = 0; $i < count($rooms); $i++)
        <x-input.checkbox :caption="$rooms[$i]->value"
                :id="'rooms_' . $rooms[$i]->code"
                name="rooms" :value="$rooms[$i]->code"
                :editData=$editDataCampus/>
        @endfor
    </x-bs.form-group>

    <x-input.date-picker caption="生年月日" id="birth_date" :editData=$editData/>
    <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$gradeList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.text caption="学年設定年度" id="grade_year" :rules=$rules :editData=$editData/>
    <x-input.select id="is_jukensei" caption="受験生フラグ" :select2=true :mastrData=$jukenFlagList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.modal-select caption="所属学校（小）" id="school_cd_e" btnCaption="学校検索" :editData=$editData />
    <x-input.modal-select caption="所属学校（中）" id="school_cd_j" btnCaption="学校検索" :editData=$editData />
    <x-input.modal-select caption="所属学校（高）" id="school_cd_h" btnCaption="学校検索" :editData=$editData />
    <x-input.text caption="生徒電話番号" id="tel_stu" :rules=$rules :editData=$editData/>
    <x-input.text caption="保護者電話番号" id="tel_par" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒メールアドレス" id="email_stu" :rules=$rules :editData=$editData/>
    <x-input.text caption="保護者メールアドレス" id="email_par" :rules=$rules :editData=$editData/>
    <x-input.select id="login_kind" caption="ログインID種別" :select2=true :mastrData=$loginKindList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.select id="stu_status" caption="会員ステータス" :select2=true :mastrData=$statusList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.date-picker caption="入会日" id="enter_date" :editData=$editData/>

    {{-- 編集画面の場合、かつ、会員ステータス＝休塾予定、休塾の場合に表示 --}}
    @if (request()->routeIs('member_mng-edit'))
    <div v-cloak>
        <x-input.date-picker caption="休塾開始日" id="recess_start_date" :editData=$editData
            vShow="form.stu_status == {{ App\Consts\AppConst::CODE_MASTER_28_2 }} || form.stu_status == {{ App\Consts\AppConst::CODE_MASTER_28_3 }}"/>
        <x-input.date-picker caption="休塾終了日" id="recess_end_date" :editData=$editData
            vShow="form.stu_status == {{ App\Consts\AppConst::CODE_MASTER_28_2 }} || form.stu_status == {{ App\Consts\AppConst::CODE_MASTER_28_3 }}"/>
    </div>

    {{-- 編集画面の場合、かつ、会員ステータス＝退会処理中、退会済の場合に表示 --}}
    <x-input.date-picker caption="退会日" id="leave_date" :editData=$editData
        vShow="form.stu_status == {{ App\Consts\AppConst::CODE_MASTER_28_4 }} || form.stu_status == {{ App\Consts\AppConst::CODE_MASTER_28_5 }}"/>
    @endif

    <x-input.text caption="外部サービス顧客ID" id="lead_id" :rules=$rules :editData=$editData/>
    <x-input.text caption="ストレージURL" id="storage_link" :rules=$rules :editData=$editData/>
    <x-input.textarea id="memo" caption="メモ" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', 1)}}" />

            @if (request()->routeIs('member_mng-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>
{{-- モーダル --}}
{{-- 所属学校検索 --}}
@include('pages.admin.modal.school_search-modal')

@stop