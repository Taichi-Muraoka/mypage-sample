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
    <p>時間割の登録を行います。</p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.select caption="校舎" id="school_kind" :select2=true :editData=$editData>
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
    </x-input.select>
    <x-input.select caption="時限" id="period_no" :select2=true :editData=$editData>
        <option value="1">1限</option>
        <option value="2">2限</option>
        <option value="3">3限</option>
    </x-input.select>
    <x-input.text caption="開始時刻" id="start_time" :rules=$rules :editData=$editData/>
    <x-input.text caption="終了時刻" id="end_time" :rules=$rules :editData=$editData/>
    <x-input.select caption="時間割区分" id="kind_cd" :select2=true :editData=$editData>
        <option value="1">通常</option>
        <option value="2">特別期間</option>
    </x-input.select>

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