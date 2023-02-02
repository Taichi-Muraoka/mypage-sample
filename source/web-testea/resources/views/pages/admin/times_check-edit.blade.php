@extends('adminlte::page')

@section('title', '回数報告編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下の回数報告の変更を行います。</p>

    <x-input.date-picker caption="報告日" id="regist_time" :editData=$editData />

    <x-bs.form-title>教師名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->name}}</p>

    {{-- チェンジイベントを取得し、授業日時と生徒名、教科を取得する --}}
    <x-input.select caption="実施月" id="report_date" :select2=true onChange="selectChangeGetMulti" :editData=$editData
        :mastrData=$reportDate />

    <x-input.select caption="教室" id="roomcd" :select2=true onChange="selectChangeGetMulti" :editData=$editData
        :mastrData=$rooms />

    <x-bs.form-title>登録済みの授業報告一覧</x-bs.form-title>

    <p class="text-muted" v-show="form.report_date == '' || form.roomcd == ''">実質月・教室を選択してください</p>

    {{-- テーブル --}}
    <x-bs.table class="mb-3" v-show="form.report_date != '' && form.roomcd != ''">
        <x-slot name="thead">
            <th width="20%">授業日時</th>
            <th>生徒名</th>
            <th width="15%">授業時間数</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in selectGetItem.class" v-cloak>
            <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.r_minutes}}</td>
        </tr>
    </x-bs.table>

    <x-bs.form-title>実施回数表示</x-bs.form-title>

    <p class="text-muted" v-show="form.report_date == '' || form.roomcd == ''">実質月・教室を選択してください</p>

    {{-- テーブル --}}
    <x-bs.table class="mb-3" v-show="form.report_date != '' && form.roomcd != ''">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>生徒名</th>
            <th width="15%">回数</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in selectGetItem.student" v-cloak>
            <x-bs.td-sp caption="生徒名">@{{item.name}}</x-bs.td-sp>
            <x-bs.td-sp caption="回数" class="not-center">@{{item.name_count}}回</x-bs.td-sp>
        </tr>
    </x-bs.table>

    <x-input.textarea caption="上記以外に実施した授業や事務作業" id="office_work" :rules=$rules :editData=$editData />

    <x-input.textarea caption="その他特記事項（テキストの購入・イレギュラーな交通費変更等）" id="other" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="times_report_id" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />

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