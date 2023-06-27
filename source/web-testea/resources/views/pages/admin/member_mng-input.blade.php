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
    <p class="edit-disp-indent">1</p>

    @else
    {{-- 登録時 --}}
    <p>会員の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="外部サービス顧客ID" id="external_id" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒名カナ" id="name_kana" :rules=$rules :editData=$editData/>
    <x-bs.form-group name="campas_groups">
        <x-bs.form-title>所属校舎</x-bs.form-title>
        {{-- 校舎チェックボックス --}}
        <x-input.checkbox caption="久我山"
                :id="'campas_group_' . '1'"
                name="campas_groups" :value="1" />
        <x-input.checkbox caption="西永福"
                :id="'campas_group_' . '2'"
                name="campas_groups" :value="2" />
        <x-input.checkbox caption="下高井戸"
                :id="'campas_group_' . '3'"
                name="campas_groups" :value="3" />
        <x-input.checkbox caption="駒込"
                :id="'campas_group_' . '4'"
                name="campas_groups" :value="4" />
        <x-input.checkbox caption="日吉"
                :id="'campas_group_' . '5'"
                name="campas_groups" :value="5" />
        <x-input.checkbox caption="自由が丘"
                :id="'campas_group_' . '6'"
                name="campas_groups" :value="6" />
    </x-bs.form-group>

    {{-- <x-input.select caption="学年" id="grade_cd" :select2=true :blank=false :editData=$editData :mastrData=$classes/> --}}
    <x-input.select id="grade_cd" caption="学年" :select2=true >
        <option value="1">高3</option>
        <option value="2">高2</option>
        <option value="3">高1</option>
        <option value="4">中3</option>
        <option value="5">中2</option>
        <option value="6">中1</option>
    </x-input.select>
    <x-input.text caption="学年設定年度" id="grade_year" :rules=$rules :editData=$editData/>
    <x-input.select caption="受験生フラグ" id="is_jukensei" :select2=true :blank=false :editData=$editData>
        <option value="1">非受験生</option>
        <option value="2">受験生</option>
    </x-input.select>
    <x-input.modal-select caption="所属学校（小）" id="school_cd_e" btnCaption="学校検索" />
    <x-input.modal-select caption="所属学校（中）" id="school_cd_j" btnCaption="学校検索" />
    <x-input.modal-select caption="所属学校（高）" id="school_cd_h" btnCaption="学校検索" />
    <x-input.text caption="生徒電話番号" id="tel_stu" :rules=$rules :editData=$editData/>
    <x-input.text caption="保護者電話番号" id="tel_par" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒メールアドレス" id="email_stu" :rules=$rules :editData=$editData/>
    <x-input.text caption="保護者メールアドレス" id="email_par" :rules=$rules :editData=$editData/>
    <x-input.select caption="ログインID種別" id="login_kind" :select2=true :blank=false :editData=$editData>
        <option value="1">生徒</option>
        <option value="2">保護者</option>
    </x-input.select>
    <x-input.select caption="会員ステータス" id="stu_status" :select2=true :blank=false :editData=$editData>
        <option value="0">見込客</option>
        <option value="1">在籍</option>
        <option value="2">退会処理中</option>
        <option value="3">退会</option>
    </x-input.select>
    <x-input.date-picker caption="入会日" id="enter_date" />

    @if (request()->routeIs('member_mng-edit'))
    <x-input.date-picker caption="退会日" id="leave_date" />
    @endif

    <x-input.text caption="ストレージURL" id="storage_url" :rules=$rules :editData=$editData/>

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', 1)}}" />

            @if (request()->routeIs('member_mng-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                {{-- 削除機能なし --}}
                {{-- <x-button.submit-delete /> --}}
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