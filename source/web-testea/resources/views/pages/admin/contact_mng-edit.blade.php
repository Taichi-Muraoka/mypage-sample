@extends('adminlte::page')

@section('title', '問い合わせ編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <x-input.date-picker caption="問い合わせ日" id="regist_time" :editData=$editData />

    {{-- <x-input.select id="roomcd" caption="宛先" :select2=true :editData=$editData :mastrData=$roomList /> --}}
    <x-input.select id="roomcd" caption="宛先" :select2=true >
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    <x-input.text caption="問い合わせ件名" id="title" :rules=$rules :editData=$editData />

    <x-input.textarea caption="問い合わせ内容" id="text" :rules=$rules :editData=$editData />

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-input.date-picker caption="回答日" id="answer_time" :editData=$editData />

    {{-- <x-input.select id="adm_id" caption="回答者名" :select2=true :editData=$editData :mastrData=$admList /> --}}
    <x-input.select id="adm_id" caption="回答者名" :select2=true >
        <option value="1">久我山　教室長</option>
        <option value="2">西永福　教室長</option>
        <option value="3">下高井戸　教室長</option>
        <option value="4">駒込　教室長</option>
        <option value="5">日吉　教室長</option>
        <option value="6">自由が丘　教室長</option>
    </x-input.select>

    <x-input.textarea caption="回答内容" id="answer_text" :rules=$rules :editData=$editData />

    {{-- <x-input.select id="contact_state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$contactState /> --}}
    <x-input.select caption="ステータス" id="state" :select2=true :editData=$editData>
        <option value="1">未回答</option>
        <option value="3">回答済</option>
    </x-input.select>

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