@extends('adminlte::page')

@section('title', '会員情報取込')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>
        入会時もしくは授業追加、コース追加・変更、短期個別講習申込により変更された生徒の基本情報・契約情報・スケジュールを取り込みます。
    </p>

    <x-input.file caption="会員情報・契約情報・スケジュールファイル" id="upload_file_member" />

    <x-bs.callout>
        ファイル形式：ZIP形式
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.member_import-modal', ['modal_send_confirm' => true])

@stop