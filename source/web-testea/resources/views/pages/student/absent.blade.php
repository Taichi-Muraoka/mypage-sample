@extends('adminlte::page')

@section('title', '欠席申請')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>欠席申請を行います。欠席したい授業日・欠席理由を入力してください。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- チェンジイベントを取得し、校舎と講師を取得する --}}
    <x-input.select caption="授業日時" id="id" :select2=true onChange="" :editData=$editData >
        <option value="1">2023/04/17 16:00</option>
        <option value="2">2023/04/24 16:00</option>
        <option value="3">2023/05/01 16:00</option>
    </x-input.select>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th class="t-minimum" width="25%">校舎</th>
            <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
        </tr>
        <tr>
            <th>講師</th>
            <td><span v-cloak>@{{selectGetItem.teacher_name}}<span v-if="selectGetItem.teacher_name">先生</span></span></td>
        </tr>
    </x-bs.table>

    <x-input.textarea caption="欠席理由" id="absent_reason" :rules=$rules />

    <x-bs.callout title="欠席の際の注意事項" type="warning">
        授業日の前日までに申請を行ってください。<br>
        授業日当日の欠席連絡につきましては、0120-XX-XXXX までお電話いただきますようお願いします。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop