@extends('adminlte::page')

@section('title', (request()->routeIs('karte-edit')) ? '生徒カルテ編集' : '生徒カルテ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の生徒カルテの{{(request()->routeIs('karte-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('karte-edit'))
    {{-- 編集時 --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">登録日時</th>
            <td>2023/01/10 17:00</td>
        </tr>
        <tr>
            <th>登録者名</th>
            <td>教室管理者（仙台駅前）</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-input.select caption="カルテ種別" id="karte_kind" :select2=true :editData="$editData">
        <option value="1" selected>面談記録</option>
        <option value="2">電話記録</option>
        <option value="3">その他</option>
    </x-input.select>

    @else
    {{-- 登録時 --}}
    <x-input.select caption="教室" id="roomcd" :select2=true :editData="$editData">
        <option value="1">仙台駅前</option>
        <option value="2">定禅寺</option>
        <option value="3">長町南</option>
    </x-input.select>

    <x-input.select caption="生徒名" id="sid" :select2=true :editData=$editData>
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
        <option value="4">CWテスト生徒４</option>
        <option value="5">CWテスト生徒５</option>
    </x-input.select>

    <x-input.select caption="カルテ種別" id="karte_kind" :select2=true :editData="$editData">
        <option value="1">面談記録</option>
        <option value="2">電話記録</option>
        <option value="3">その他</option>
    </x-input.select>

    @endif

    <x-input.textarea caption="内容" id="karte_text" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('karte-edit'))
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