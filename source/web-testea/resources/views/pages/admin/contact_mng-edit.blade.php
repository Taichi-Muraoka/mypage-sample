@extends('adminlte::page')

@section('title', '問い合わせ編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <x-input.date-picker caption="問い合わせ日" id="regist_time" :editData=$editData />

    <x-input.select id="campus_cd" caption="宛先校舎" :select2=true :editData=$editData :mastrData=$roomList
        :select2Search=false :blank=true />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-input.text caption="問い合わせ件名" id="title" :rules=$rules :editData=$editData />

    <x-input.textarea caption="問い合わせ内容" id="text" :rules=$rules :editData=$editData />

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-input.date-picker caption="回答日" id="answer_time" :editData=$editData />

    <x-input.select id="adm_id" caption="回答者名" :select2=true :editData=$editData :mastrData=$admList />

    <x-input.textarea caption="回答内容" id="answer_text" :rules=$rules :editData=$editData />

    <x-input.select id="contact_state" caption="ステータス" :editData=$editData :mastrData=$contactState
        :select2=true :select2Search=false :blank=false />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        回答を登録すると生徒へメールが送信されます。（ステータスを未回答から回答済に更新時）
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="contact_id" :editData=$editData />

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