@extends('adminlte::page')

@section('title', 'ギフトカード付与情報編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>ギフトカード付与情報を変更します。</p>

    <x-input.date-picker caption="付与日" id="grant_time" :editData=$editData />

    <x-input.date-picker caption="申請日" id="apply_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-input.text caption="ギフトカード名" id="card_name" :editData=$editData :rules=$rules />

    <x-input.text caption="割引内容" id="discount" :editData=$editData :rules=$rules />

    <x-input.date-picker caption="使用期間 開始日" id="term_start" :editData=$editData />

    <x-input.date-picker caption="使用期間 終了日" id="term_end" :editData=$editData />

    <x-input.select id="card_state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$statusList />

    <x-input.textarea caption="事務局コメント" id="comment" :editData=$editData :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="card_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop