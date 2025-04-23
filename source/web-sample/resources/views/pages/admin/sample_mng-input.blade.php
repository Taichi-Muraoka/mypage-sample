@extends('adminlte::page')

@section('title', (request()->routeIs('sample_mng-edit')) ? 'サンプルデータ編集' : 'サンプルデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('sample_mng-edit'))
    {{-- 編集時 --}}
    <p>以下のサンプル情報について編集を行います。</p>
    @else
    {{-- 登録時 --}}
    <p>サンプル情報の登録を行います。</p>
    @endif

    <x-input.date-picker caption="登録日" id="regist_date" :editData=$editData />

    @if (request()->routeIs('sample_mng-edit'))
    {{-- 編集時 生徒変更不可とし表示のみとする場合 --}}
    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>
    {{-- hidden --}}
    <x-input.hidden id="student_id" :editData=$editData />
    @else
    {{-- 登録時 --}}
    <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$students :editData=$editData
        :select2Search=true :blank=true />
    @endif

    <x-input.text caption="サンプル件名" id="sample_title" :rules=$rules :editData=$editData />

    <x-input.textarea caption="サンプルテキスト" id="sample_text" :rules=$rules :editData=$editData />

    <x-input.select id="sample_state" caption="ステータス" :editData=$editData :mastrData=$sampleStateList
        :select2=true :select2Search=false :blank=false />

    @if (request()->routeIs('sample_mng-edit'))
    {{-- 編集時にのみ、登録者名を表示する場合 --}}
    {{-- 余白 --}}
    <div class="mb-4"></div>
    <x-bs.form-title>登録者名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->adm_name}}</p>
    @endif

    {{-- hidden --}}
    <x-input.hidden id="sample_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('sample_mng-edit'))
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
