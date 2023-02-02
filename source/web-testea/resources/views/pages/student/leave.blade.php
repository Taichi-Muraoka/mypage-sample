@extends('adminlte::page')

@section('title', '退会申請')

@section('content')

{{-- 退会申請済みか判定 --}}
@if ($isLeave)

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>退会申請を行います。</p>

    <x-input.textarea caption="退会を希望する理由をご入力ください。" id="leave_reason" placeholder="理由" :rules=$rules />

    <x-bs.callout title="退会の際の注意事項" type="warning">
        確認のため、教室または事務局より別途ご連絡いたします。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-edit caption="送信" />
        </div>
    </x-slot>

</x-bs.card>

@else

{{-- 退会済み時 --}}
<div id="app-form">
    <x-bs.callout type="warning" :margin=false>
        すでに退会申請済みです。
    </x-bs.callout>
</div>

@endif

@stop