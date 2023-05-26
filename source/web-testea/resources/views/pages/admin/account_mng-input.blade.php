@extends('adminlte::page')

@section('title', (request()->routeIs('account_mng-edit')) ? '事務局アカウント編集' : '事務局アカウント登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form="true">

    <p>以下の事務局アカウントの{{(request()->routeIs('account_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.text caption="メールアドレス" id="email" :rules=$rules :editData=$editData />

    <x-input.pw caption="パスワード" id="password" :editData=$editData :rules=$rules />

    <x-input.text caption="氏名" id="name" :rules=$rules :editData=$editData />

    <x-input.select caption="管理校舎" id="roomcd" :select2=true :mastrData=$rooms :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="adm_id" :editData=$editData />
    <x-input.hidden id="before_email" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('account_mng-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                {{-- ログインユーザの場合は削除不可として非活性にする --}}
                <x-button.submit-delete :disabled=$delBtnSts />
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