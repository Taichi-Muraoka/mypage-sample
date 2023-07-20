@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-edit')) ? '講師編集' : '講師登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の講師について、編集を行います。</p>
    <x-bs.form-title>講師ID</x-bs.form-title>
    <p class="edit-disp-indent">1</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>講師の基本情報を登録します。</p>
    @endif

    {{-- 共通項目 --}}
    <x-input.text caption="講師名" id="name" :rules=$rules />
    <x-input.text caption="電話番号" id="tel" :rules=$rules />
    <x-input.text caption="メールアドレス" id="email" :rules=$rules />
    <x-input.date-picker caption="生年月日" id="birth_date" />
    <x-input.select id="grade_cd" caption="学年" :select2=true :select2Search=false >
        <option value="1">大1</option>
        <option value="2">大2</option>
        <option value="3">大3</option>
        <option value="4">大4</option>
        <option value="9">その他</option>
    </x-input.select>
    <x-input.text caption="学年設定年度" id="grade_year" :rules=$rules :editData=$editData/>
    <x-input.date-picker caption="勤務開始日" id="enter_date" />

    <x-input.text caption="授業時給（ベース給）" id="hourly_wage" :rules=$rules :editData=$editData/>

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

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 講師ステータス--}}
    <x-input.select id="tutor_status" caption="講師ステータス" :select2=false >
        <option value="1">在籍</option>
        <option value="2">退職処理中</option>
        <option value="3">退職</option>
    </x-input.select>
    @endif

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

@stop