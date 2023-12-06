@extends('adminlte::page')

@section('title', '追加授業依頼')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>追加授業の依頼を行います。</p>

    <x-input.select caption="校舎" id="campus_cd" :select2=true :mastrData=$rooms :select2Search=false :blank=false />

    <x-input.textarea caption="追加授業の希望内容（希望の授業日時や教科等）" id="request" :rules=$rules />

    <x-bs.callout type="warning">
        教室長が授業スケジュールを調整し、ご連絡します。<br>
        通常担当の講師とは別の講師で対応する場合がありますのでご了承ください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop