@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-edit')) ? '講師編集' : '講師登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 編集画面の場合のみ、講師情報詳細を経由する --}}
@if (request()->routeIs('tutor_mng-edit'))
@section('parent_page', route('tutor_mng-detail', 1))
@section('parent_page_title', '講師詳細情報')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の講師について、編集を行います。</p>
    <x-bs.form-title>講師ID</x-bs.form-title>
    <p class="edit-disp-indent">1</p>

    @else
    {{-- 登録時 --}}
    <p>講師の基本情報を登録します。</p>
    @endif

    {{-- 共通項目 --}}
    <x-input.text caption="講師名" id="name" :rules=$rules />
    <x-input.text caption="講師名かな" id="name_kana" :rules=$rules />
    <x-input.text caption="電話番号" id="tel" :rules=$rules />
    <x-input.text caption="メールアドレス" id="email" :rules=$rules />
    <x-input.text caption="住所" id="address" :rules=$rules />
    <x-input.date-picker caption="生年月日" id="birth_date" />
    <x-input.select id="gender_cd" caption="性別" :select2=true :select2Search=false >
        <option value="1">男性</option>
        <option value="2">女性</option>
        <option value="3">その他</option>
    </x-input.select>
    <x-input.select id="grade_cd" caption="学年" :select2=true :select2Search=false >
        <option value="1">大学1年</option>
        <option value="2">大学2年</option>
        <option value="3">大学3年</option>
        <option value="4">大学4年</option>
        <option value="5">大学卒</option>
        <option value="6">M1</option>
        <option value="7">M2</option>
        <option value="8">修士修了</option>
        <option value="9">D1</option>
        <option value="10">D2</option>
        <option value="11">D3</option>
        <option value="12">博士修了</option>
        <option value="13">その他</option>
    </x-input.select>

    <x-input.text caption="学年設定年度" id="grade_year" :rules=$rules :editData=$editData/>

    <x-input.modal-select caption="所属大学" id="school_cd_u" btnCaption="学校検索" :editData=$editData />
    <x-input.modal-select caption="出身高校" id="school_cd_h" btnCaption="学校検索" :editData=$editData />
    <x-input.modal-select caption="出身中学" id="school_cd_j" btnCaption="学校検索" :editData=$editData />

    <x-input.text caption="授業時給（ベース給）" id="hourly_wage" :rules=$rules :editData=$editData/>

    @if (request()->routeIs('tutor_mng-edit'))
    <x-input.select id="tutor_status" caption="講師ステータス" :select2=true :select2Search=false >
        <option value="1">在籍</option>
        <option value="2">退職処理中</option>
        <option value="3">退職済</option>
    </x-input.select>
    @endif

    <x-input.date-picker caption="勤務開始日" id="enter_date" />

    @if (request()->routeIs('tutor_mng-edit'))
    <x-input.date-picker caption="退職日" id="leave_date" />
    @endif

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-bs.card>
        <x-bs.form-title>担当科目選択</x-bs.form-title>

        <x-bs.form-group name="subject_groups_p">
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($subjectGroup); $i++)
                <x-input.checkbox :caption="$subjectGroup[$i]"
                        :id="'subject_group_p' . $subjectGroup[$i]"
                        name="subject_groups_p" :value="$subjectGroup[$i]" />
                    @if (($i+1) % 5 == 0) <br><br>@endif
                @endfor
        </x-bs.form-group>
    </x-bs.card>

    <x-input.textarea id="text" caption="メモ" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('tutor_mng-edit'))
            {{-- 編集時 --}}
            <x-button.back url="{{route('tutor_mng-detail', 1)}}"/>
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