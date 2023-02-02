@extends('adminlte::page')

@section('title', 'ギフトカード付与')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>ギフトカードを生徒に付与します。</p>

    {{-- 選択された教室名で生徒のプルダウンを動的に変える --}}
    <x-input.select id="roomcd" caption="教室" :select2=true :editData=$editData :mastrData=$roomList
        onChange="selectChangeGetRoom" />

    <x-input.select caption="生徒名" id="sid" :select2=true :editData=$editData>
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItem.selectItems" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-input.text caption="ギフトカード名" id="card_name" :editData=$editData :rules=$rules />

    <x-input.text caption="割引内容" id="discount" :editData=$editData :rules=$rules />

    <x-input.date-picker caption="使用期間 開始日" id="term_start" :editData=$editData />

    <x-input.date-picker caption="使用期間 終了日" id="term_end" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            <div class="d-flex justify-content-end">
                <x-button.submit-new />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop