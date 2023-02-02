@extends('adminlte::page')

@section('title', 'パスワード変更')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>パスワードを変更します。</p>

    <x-input.pw caption="現在のパスワード" id="current_pass" :editData=$editData :rules=$rules />

    <x-input.pw caption="新しいパスワード" id="new_pass" :editData=$editData :rules=$rules />

    <x-input.pw caption="新しいパスワード(確認)" id="new_pass_confirmation" placeholder="確認" :editData=$editData :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-edit caption="送信" />
        </div>
    </x-slot>

</x-bs.card>

@stop