@extends('adminlte::page')

@section('title', (request()->routeIs('member_mng-edit')) ? '会員編集' : '会員登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('member_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の会員について、編集を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">会員No</th>
            <td>1</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>会員の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="生徒名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒名カナ" id="name_kana" :rules=$rules :editData=$editData/>
    {{-- <x-input.select caption="学年" id="grade_cd" :select2=true :blank=false :editData=$editData :mastrData=$classes/> --}}
    <x-input.select id="grade_cd" caption="学年" :select2=true >
        <option value="1">高3</option>
        <option value="2">高2</option>
        <option value="3">高1</option>
        <option value="4">中3</option>
        <option value="5">中2</option>
        <option value="6">中1</option>
    </x-input.select>
    <x-input.text caption="所属学校（小）" id="school_cd_e" :rules=$rules :editData=$editData/>
    <x-input.text caption="所属学校（中）" id="school_cd_j" :rules=$rules :editData=$editData/>
    <x-input.text caption="所属学校（高）" id="school_cd_h" :rules=$rules :editData=$editData/>
    <x-input.select caption="受験生フラグ" id="is_jukensei" :select2=true :blank=false :editData=$editData>
        <option value="1">非受験生</option>
        <option value="2">受験生</option>
    </x-input.select>
    <x-input.text caption="生徒電話番号" id="tel_stu" :rules=$rules :editData=$editData/>
    <x-input.text caption="保護者電話番号" id="tel_par" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒メールアドレス" id="email_stu" :rules=$rules :editData=$editData/>
    <x-input.text caption="保護者メールアドレス" id="email_par" :rules=$rules :editData=$editData/>
    <x-input.select caption="ログインID種別" id="login_kind" :select2=true :blank=false :editData=$editData>
        <option value="1">生徒</option>
        <option value="2">保護者</option>
    </x-input.select>
    <x-input.select caption="会員ステータス" id="stu_status" :select2=true :blank=false :editData=$editData>
        <option value="0">見込み客</option>
        <option value="1">入会</option>
        <option value="2">退会処理中</option>
        <option value="3">退会</option>
    </x-input.select>
    <x-input.date-picker caption="入会日" id="enter_date" />
    <x-input.date-picker caption="退会日" id="leave_date" />

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

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

@stop