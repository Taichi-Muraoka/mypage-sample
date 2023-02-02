@extends('adminlte::page')

@section('title', '振替連絡編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="transfer_apply_id" :editData=$editData />

    <p>以下の振替連絡について変更を行います。</p>

    <x-input.date-picker caption="申請日" id="apply_time" :editData=$editData />

    <x-bs.form-title>教師名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->teacher_name}}</p>

    {{-- チェンジイベントを取得し、教室と教師を取得する --}}
    <x-input.select caption="生徒" id="sid" :select2=true onChange="selectChangeGetMulti" :mastrData=$students
        :editData=$editData />

    {{-- チェンジイベントを取得し、授業日時と生徒名、教科を取得する --}}
    {{-- hidden 退避用--}}
    <x-input.hidden id="_id" :editData=$editData />
    <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
        {{-- 生徒を選択したら動的にリストを作成する --}}
        <option v-for="item in selectGetItem.selectItems" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th class="t-minimum" width="25%">教室</th>
            <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
        </tr>
    </x-bs.table>

    <x-input.date-picker caption="振替日" id="transfer_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="transfer_time" :rules=$rules :editData=$editData />

    <x-input.textarea caption="振替理由" id="transfer_reason" :rules=$rules :editData=$editData />

    <x-input.select id="state" caption="ステータス" :select2=true :mastrData=$states :select2Search=false
        :editData=$editData />

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