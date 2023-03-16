@extends('adminlte::page')

@section('title', (request()->routeIs('prospect-edit')) ? '見込み客編集' : '見込み客登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('prospect-edit'))
    {{-- 編集時 --}}
    <p>以下の見込み客について、編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>見込み客の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.select caption="問い合わせ項目" id="inquiry_item" :select2=true :editData=$editData>
        <option value="1">無料相談</option>
        <option value="2">無料体験授業</option>
        <option value="3">特別企画申込</option>
        <option value="4">その他</option>
    </x-input.select>
    <x-input.select caption="希望校舎" id="schools" :select2=true :editData=$editData>
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
        <option value="7">WEB個</option>
        <option value="8">算数道場</option>
        <option value="9">未定</option>
    </x-input.select>
    <x-input.select caption="学年" id="cls_cd" :select2=true :editData=$editData :mastrData=$classes/>
    <x-input.text caption="生徒名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒名カナ" id="name_kana" :rules=$rules :editData=$editData/>
    <x-input.text caption="メールアドレス" id="email" :rules=$rules :editData=$editData/>
    <x-input.text caption="電話番号" id="tel" :rules=$rules :editData=$editData/>
    <x-input.textarea caption="問い合わせ内容・現在お悩みの点" id="inquiry_matter" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('prospect-edit'))
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