@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_booth-edit')) ? 'ブースマスタデータ編集' : 'ブースマスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_booth-edit'))
    {{-- 編集時 --}}
    <p>以下のブース情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>ブース情報の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    @can('roomAdmin')
    {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=false />
    @else
    {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=true />
    @endcan

    <x-input.text id="booth_cd" caption="ブースコード" :rules=$rules :editData=$editData/>

    <x-input.select id="usage_kind" caption="用途種別" :select2=true :mastrData=$kindList :editData=$editData
        :select2Search=false :blank=true />

    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="表示順" id="disp_order" :rules=$rules :editData=$editData/>

    {{-- hidden --}}
    <x-input.hidden id="booth_id" :editData=$editData />

    <x-bs.callout type="warning">
        ブースコード「000」および「999」はシステム内で使用しているコードのため、登録できません。<br>
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_booth-edit'))
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