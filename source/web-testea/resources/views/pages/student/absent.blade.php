@extends('adminlte::page')

@section('title', '欠席連絡（1対多）')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>1対多授業の欠席連絡を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- チェンジイベントを取得し、テーブル表示に必要な情報を取得する --}}
    <x-input.select caption="授業日・時限" id="schedule_id" :select2=true onChange="selectChangeGet" :mastrData=$scheduleList
        :select2Search=false />

    {{-- 詳細を表示 --}}
    <div v-cloak>
        <x-bs.table :hover=false :vHeader=true class="mb-4">
            <tr>
                <th width="35%">校舎</th>
                <td><span v-cloak>@{{selectGetItem.campus_name}}</span></td>
            </tr>
            <tr>
                <th>講師</th>
                <td><span v-cloak>@{{selectGetItem.tutor_name}}<span v-if="selectGetItem.teacher_name">先生</span></span>
                </td>
            </tr>
            <tr>
                <th>教科</th>
                <td><span v-cloak>@{{selectGetItem.subject_name}}</span></td>
            </tr>
            <tr>
                <th>校舎連絡先</th>
                <td><span v-cloak><a href="tel:@{{selectGetItem.tel_campus}}">@{{selectGetItem.tel_campus}}</a></span>
                </td>
            </tr>
        </x-bs.table>
    </div>

    <x-input.textarea caption="欠席理由" id="absent_reason" :rules=$rules />

    <x-bs.callout title="欠席の際の注意事項" type="warning">
        授業日の前日までに連絡を行ってください。<br>
        授業日当日の欠席連絡につきましては、各校舎までお電話いただきますようお願いします。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop