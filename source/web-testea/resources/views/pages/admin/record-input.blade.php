@extends('adminlte::page')

@section('title', (request()->routeIs('record-edit')) ? '連絡記録編集' : '連絡記録登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

@section('parent_page2', route('record', $editData['sid']))

@section('parent_page_title2', '連絡記録一覧')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の連絡記録の{{(request()->routeIs('record-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('record-edit'))
    {{-- 編集時 --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">対応日時</th>
            <td>2023/01/10 17:00</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>担当者名</th>
            <td>山田　太郎</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-input.select caption="記録種別" id="karte_kind" :select2=true :editData="$editData">
        <option value="1" selected>面談記録</option>
        <option value="2">電話記録</option>
        <option value="3">その他</option>
    </x-input.select>

    @else
    {{-- 登録時 --}}
    <x-input.select caption="校舎" id="roomcd" :select2=true :editData="$editData">
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">本郷</option>
    </x-input.select>

    <x-input.select caption="生徒名" id="sid" :select2=true :editData=$editData>
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
        <option value="4">CWテスト生徒４</option>
        <option value="5">CWテスト生徒５</option>
    </x-input.select>

    <x-input.select caption="連絡種別" id="karte_kind" :select2=true :editData="$editData">
        <option value="1">面談記録</option>
        <option value="2">電話記録</option>
        <option value="3">その他</option>
    </x-input.select>

    <x-input.select caption="担当者名" id="admin_id" :select2=true :editData=$editData>
        <option value="1">山田　太郎</option>
        <option value="2">鈴木　花子</option>
    </x-input.select>

    @endif

    <x-input.textarea caption="内容" id="karte_text" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('record', $editData['sid'])}}" />

            @if (request()->routeIs('record-edit'))
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