@extends('adminlte::page')

@section('title', '退会申請編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下の退会申請について変更を行います。</p>

    {{-- id --}}
    <x-input.hidden id="leave_apply_id" :editData=$editData />

    <x-input.date-picker caption="申請日" id="apply_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-input.textarea caption="退会理由" id="leave_reason" :rules=$rules :editData=$editData />

    <x-input.select id="leave_state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$statusList />

    <x-input.textarea caption="事務局コメント" id="comment" :rules=$rules :editData=$editData />

    <x-bs.callout title="ステータス変更時の注意事項" type="danger">
        ステータスを「退会済」にすると、対象生徒のアカウントがロックされ、対象生徒に関連する情報が削除されます。<br>
        画面からの復元はできませんのでご注意ください。
    </x-bs.callout>

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