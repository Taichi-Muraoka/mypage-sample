@extends('adminlte::page')

@section('title', 'コース変更・授業追加申請編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下のコース変更・授業追加申請について変更を行います。</p>

    <x-input.date-picker caption="申請日" id="apply_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>
    
    <x-input.select caption="追加・変更種別" id="change_type" :select2=true :editData=$editData :mastrData=$changeType />

    <x-input.textarea caption="追加・変更希望内容" id="changes_text" :editData=$editData :rules=$rules />

    <x-input.select id="changes_state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$statusList />

    <x-input.textarea caption="事務局コメント" id="comment" :editData=$editData :rules=$rules />

    <x-input.hidden id="change_id" :editData=$editData />

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