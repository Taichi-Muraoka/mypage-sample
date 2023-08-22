@extends('adminlte::page')

@section('title', (request()->routeIs('notice_template-edit')) ? 'お知らせ定型文編集' : 'お知らせ定型文登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のお知らせ定型文の{{(request()->routeIs('notice_template-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.text id="template_name" caption="定型文名" :rules=$rules :editData=$editData />

    <x-input.text id="title" caption="タイトル" :rules=$rules :editData=$editData />

    <x-input.textarea id="text" caption="本文" :rules=$rules :editData=$editData />

    {{-- 本番用 --}}
    {{-- <x-input.select id="notice_type" caption="お知らせ種別" :select2=true :editData=$editData :mastrData=$typeList /> --}}

    {{-- モック用 --}}
    <x-input.select id="notice_type" caption="お知らせ種別" :select2=true :editData=$editData >
        <option value="4">その他</option>
        <option value="5">面談</option>
        <option value="6">特別期間講習</option>
        <option value="7">成績登録</option>
        <option value="8">請求</option>
        <option value="9">給与</option>
        <option value="10">追加請求</option>
    </x-input.select>

    {{-- 現在の表示順の最大値＋1をデフォルト値として設定 --}}
    <x-input.text id="order_code" caption="表示順" :rules=$rules :editData=$editData />

    {{-- ID --}}
    <x-input.hidden id="template_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('notice_template-edit'))
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