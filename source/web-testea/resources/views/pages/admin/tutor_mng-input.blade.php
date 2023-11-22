@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-edit')) ? '講師編集' : '講師登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 編集画面の場合のみ、講師情報詳細を経由する --}}
@if (request()->routeIs('tutor_mng-edit'))
@section('parent_page', route('tutor_mng-detail', $editData['tutor_id']))
@section('parent_page_title', '講師詳細情報')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の講師について、編集を行います。</p>
    <x-bs.form-title>講師ID</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData['tutor_id']}}</p>

    @else
    {{-- 登録時 --}}
    <p>講師の基本情報を登録します。</p>
    @endif

    {{-- 共通項目 --}}
    <x-input.text caption="講師名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="講師名かな" id="name_kana" :rules=$rules :editData=$editData/>
    <x-input.text caption="電話番号" id="tel" :rules=$rules :editData=$editData/>
    <x-input.text caption="メールアドレス" id="email" :rules=$rules :editData=$editData/>
    <x-input.text caption="住所" id="address" :rules=$rules :editData=$editData/>
    <x-input.date-picker caption="生年月日" id="birth_date" :editData=$editData/>
    <x-input.select id="gender_cd" caption="性別" :select2=true :mastrData=$genderList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$gradeList :editData=$editData
        :select2Search=false :blank=true />
    <x-input.text caption="学年設定年度" id="grade_year" :rules=$rules :editData=$editData />
    <x-input.modal-select caption="所属大学" id="school_cd_u" btnCaption="学校検索" :editData=$editData />
    <x-input.modal-select caption="出身高校" id="school_cd_h" btnCaption="学校検索" :editData=$editData />
    <x-input.modal-select caption="出身中学" id="school_cd_j" btnCaption="学校検索" :editData=$editData />
    <x-input.text caption="授業時給（ベース給）" id="hourly_base_wage" :rules=$rules :editData=$editData />

    @if (request()->routeIs('tutor_mng-edit'))
    <x-input.select id="tutor_status" caption="講師ステータス" :select2=true :mastrData=$statusList :editData=$editData
        :select2Search=false :blank=true />
    @endif

    <x-input.date-picker caption="勤務開始日" id="enter_date" :editData=$editData/>

    @if (request()->routeIs('tutor_mng-edit'))
    @if ($editData['tutor_status'] != AppConst::CODE_MASTER_29_1)
    <x-input.date-picker caption="退職日" id="leave_date" :editData=$editData/>
    @endif
    @endif

    <x-bs.card>
        <x-bs.form-title>担当教科選択</x-bs.form-title>
        <x-bs.form-group name="subject_groups">
            {{-- 教科チェックボックス --}}
            @for ($i = 0; $i < count($subjectGroup); $i++)
            <x-input.checkbox :caption="$subjectGroup[$i]->value"
                :id="'subject_group_' . $subjectGroup[$i]->code"
                name="subject_groups" :value="$subjectGroup[$i]->code"
                :editData=$editDataSubject />
            {{-- 10個区切りで改行する --}}
            @if (($i+1) % 10 == 0) <br><br>@endif
            @endfor
        </x-bs.form-group>
    </x-bs.card>

    <x-input.textarea id="memo" caption="メモ" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('tutor_mng-edit'))
            {{-- 編集時 --}}
            <x-button.back url="{{route('tutor_mng-detail', $editData['tutor_id'])}}" />
            <div class="d-flex justify-content-end">
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.back />
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.school_search-modal')

@stop