@extends('adminlte::page')

@section('title', '問い合わせ')

{{-- 子ページ --}}
@section('child_page', true)

{{--
   問い合わせ
--}}
@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>教室または本部へ問い合わせを行います。</p>

    <x-input.select caption="宛先" id="roomcd" :select2=true >
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">本郷</option>
    </x-input.select>

    <x-input.text caption="問い合わせ件名" id="title" :rules=$rules />

    <x-input.textarea caption="問い合わせ内容" id="text" :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop