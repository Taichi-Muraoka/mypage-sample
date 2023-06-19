@extends('adminlte::page')

@section('title', '欠席申請編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')


{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下の欠席申請について変更を行います。</p>

    <x-input.date-picker caption="申請日" id="apply_date" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    {{-- <p class="edit-disp-indent">{{$editData->name}}</p> --}}
    <p class="edit-disp-indent">CWテスト生徒１</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- チェンジイベントを取得し、校舎と講師を取得する --}}
    {{-- <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData
        :mastrData=$scheduleMaster /> --}}
    <x-input.select caption="授業日時" id="id" :select2=true :editData="$editData">
        <option value="1">2023/05/15 16:00</option>
        <option value="2">2023/05/22 16:00</option>
        <option value="3">2023/05/29 16:00</option>
    </x-input.select>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="15%">校舎</th>
            <td><span v-cloak>久我山</span></td>
        </tr>
        <tr>
            <th>講師</th>
            <td><span v-cloak>CWテスト教師１０１</span></td>
        </tr>
        <tr>
            <th>コース名</th>
            <td><span v-cloak>集団授業</span></td>
        </tr>
    </x-bs.table>

    <x-input.textarea caption="欠席理由" id="absent_reason" :rules=$rules :editData=$editData >
        学校行事のため
    </x-input.textarea>

    {{-- <x-input.select id="state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$statusList /> --}}
    <x-input.select caption="ステータス" id="state" :select2=true :editData=$editData>
        <option value="1">未対応</option>
        <option value="2">対応済</option>
    </x-input.select>

    {{-- hidden --}}
    <x-input.hidden id="absent_apply_id" :editData=$editData />

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