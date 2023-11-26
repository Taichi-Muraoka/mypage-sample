@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-campus-edit')) ? '所属情報更新' : '所属情報登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('parent_page', route('tutor_mng-detail', $editData['tutor_id']))

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
    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=false />
    <x-input.text caption="交通費(往復)" id="travel_cost" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />
    <x-input.hidden id="tutor_campus_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 講師情報に戻る --}}
            <x-button.back url="{{route('tutor_mng-detail', $editData['tutor_id'])}}" />

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