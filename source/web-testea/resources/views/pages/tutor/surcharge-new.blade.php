@extends('adminlte::page')

@section('title', '追加請求登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>授業給以外の費用について追加請求申請を行います。</p>

    <x-input.select caption="請求種別" id="kinds" :select2=true :editData="$editData">
        <option value="1">事務作業</option>
        <option value="2">その他経費</option>
    </x-input.select>

    <x-input.select caption="校舎" id="schools" :select2=true :editData=$editData>
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    {{-- 事務作業の場合 --}}
    <div v-show="form.kinds == 1">
        <x-input.date-picker caption="実施日" id="date" />
        <x-input.text caption="開始時刻" id="start_time" :rules=$rules />
        <x-input.text caption="時間（分）" id="time" :rules=$rules />
        <x-input.textarea caption="内容（作業・費目等）" id="text" :rules=$rules />
    </div>

    {{-- その他経費の場合 --}}
    <div v-show="form.kinds == 2">
        <x-input.text caption="金額" id="expense" :rules=$rules />
        <x-input.textarea caption="内容（作業・費目等）" id="text" :rules=$rules />
    </div>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop