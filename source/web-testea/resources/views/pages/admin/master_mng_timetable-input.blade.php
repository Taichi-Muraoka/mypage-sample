@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_timetable-edit')) ? '時間割マスタデータ編集' : '時間割マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_timetable-edit'))
    {{-- 編集時 --}}
    <p>以下の時間割情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>時間割情報の登録を行います。</p>
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
    <x-input.select caption="時間割区分" id="timetable_kind" :select2=true :mastrData=$timetablekind :editData=$editData
        :select2Search=false :blank=true />
    <x-input.select caption="時限" id="period_no" :select2=true :mastrData=$periodNo :editData=$editData
        :select2Search=false :blank=true />
    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData/>
    <x-input.time-picker caption="終了時刻" id="end_time" :rules=$rules :editData=$editData/>

    {{-- hidden --}}
    <x-input.hidden id="timetable_id" :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_timetable-edit'))
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