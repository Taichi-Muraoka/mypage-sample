@extends('adminlte::page')

@section('title', (request()->routeIs('member_mng-calendar-edit')) ? '授業スケジュール編集' : '授業スケジュール登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-calendar', $editData['sid']))

@section('parent_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />
    <x-input.hidden id="student_schedule_id" :editData=$editData />

    <x-slot name="card_title">
        CWテスト生徒１
    </x-slot>

    <p>授業スケジュールの{{(request()->routeIs('member_mng-calendar-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('karte-edit'))
    {{-- 編集時 --}}

    @else
    {{-- 登録時 --}}
    <x-input.select id="roomcd" caption="校舎" :select2=true >
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    <x-input.select caption="講師名" id="tid" :select2=true :editData=$editData>
        <option value="1">CWテスト講師１</option>
        <option value="2">CWテスト講師２</option>
    </x-input.select>

    <x-input.date-picker caption="授業日" id="start_date" :editData=$editData />

    <x-input.select caption="時限" id="period" :select2=true :editData="$editData">
        <option value="1">1限</option>
        <option value="2">2限</option>
        <option value="3">3限</option>
        <option value="4">4限</option>
        <option value="5">5限</option>
        <option value="6">6限</option>
        <option value="7">7限</option>
        <option value="8">8限</option>
    </x-input.select>

    <x-input.select caption="教科" id="subject" :select2=true :editData="$editData">
        <option value="1">国語</option>
        <option value="2">数学</option>
        <option value="3">理科</option>
        <option value="4">社会</option>
        <option value="5">英語</option>
    </x-input.select>

    @endif

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
            <x-button.back url="{{ route('member_mng-calendar', $editData['sid']) }}" />

            @if (request()->routeIs('member_mng-calendar-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>


</x-bs.card>



@stop