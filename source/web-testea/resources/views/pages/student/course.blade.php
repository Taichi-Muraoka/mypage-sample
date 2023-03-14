@extends('adminlte::page')

@section('title', '契約変更申請')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>契約コースの追加・変更の申請を行います。</p>

    {{-- <x-input.select caption="追加・変更種別" id="change_type" :select2=true :select2Search=false :mastrData=$codeMaster :editData=$editData /> --}}
    <x-input.select caption="追加・変更種別" id="change_type" :select2=true :select2Search=false :editData=$editData >
        <option value="1">コース変更</option>
        <option value="2">コース追加</option>
    </x-input.select>

    <x-input.textarea caption="追加・変更希望内容" id="changes_text" :rules=$rules />

    <x-bs.callout type="warning">
        ※後ほど事務局よりご連絡し、詳細をお伺いします。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            {{-- update処理を呼ぶためeditにした。キャプションは送信にした --}}
            <x-button.submit-edit caption="送信" />
        </div>
    </x-slot>

</x-bs.card>

@stop