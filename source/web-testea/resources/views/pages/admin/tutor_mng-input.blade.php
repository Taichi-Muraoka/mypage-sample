@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-edit')) ? '教師編集' : '教師登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の教師について、編集を行います。</p>
    <x-input.text caption="教師No" id="tno" :rules=$rules />
    <x-input.text caption="教師名" id="tname" :rules=$rules />
    <x-input.text caption="メールアドレス" id="tmail" :rules=$rules />

    @else
    {{-- 登録時 --}}
    <p>教師の登録を行います。</p>
    <x-input.text caption="教師No" id="tno" :rules=$rules />
    <x-input.text caption="教師名" id="tname" :rules=$rules />
    <x-input.text caption="メールアドレス" id="tmail" :rules=$rules />

    @endif

    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('tutor_mng-edit'))
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