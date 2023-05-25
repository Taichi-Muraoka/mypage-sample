@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-campus-edit')) ? '所属情報更新' : '所属情報登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('parent_page', route('tutor_mng-detail', 1))

@section('parent_page_title', '講師情報')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-campus-edit'))
    {{-- 編集時 --}}
    <p>講師の所属情報を更新します。</p>

    @else
    {{-- 登録時 --}}
    <p>講師の所属情報を登録します。</p>
    @endif

    {{-- 共通項目 --}}
    <x-input.select id="roomcd" caption="校舎" :select2=true >
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    <x-input.text caption="交通費" id="tel" :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 講師情報に戻る --}}
            <x-button.back url="{{route('tutor_mng-detail', 1)}}" />

            @if (request()->routeIs('tutor_mng-campus-edit'))
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