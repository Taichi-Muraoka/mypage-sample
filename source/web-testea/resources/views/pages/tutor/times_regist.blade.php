@extends('adminlte::page')

@section('title', '回数報告')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>回数報告を行います。</p>

    <x-input.select id="report_date" caption="実施月" :select2=true onChange="selectChangeGetMulti" :rules=$rules
        :mastrData=$reportDate />

    <x-input.select id="roomcd" caption="教室" :select2=true onChange="selectChangeGetMulti" :rules=$rules
        :mastrData=$rooms />

    <x-bs.form-title>登録済みの授業報告一覧</x-bs.form-title>

    <p class="text-muted" v-show="form.report_date == '' || form.roomcd == ''">実施月・教室を選択してください</p>

    {{-- テーブル --}}
    <x-bs.table class="mb-3" :smartPhone=true v-show="form.report_date != '' && form.roomcd != ''">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">授業日時</th>
            <th>生徒名</th>
            <th width="15%">授業時間数</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in selectGetItem.class" v-cloak>
            <x-bs.td-sp caption="授業日時">@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.name}}</x-bs.td-sp>
            <x-bs.td-sp caption="授業時間数" class="not-center">@{{item.r_minutes}}</x-bs.td-sp>
        </tr>

    </x-bs.table>

    <x-bs.form-title>実施回数表示</x-bs.form-title>

    {{-- 実施月を選択後、テーブルを表示する --}}
    <p class="text-muted" v-show="form.report_date == '' || form.roomcd == ''">実施月・教室を選択してください</p>

    {{-- テーブル --}}
    <x-bs.table class="mb-3" :smartPhone=true v-show="form.report_date != '' && form.roomcd != ''">

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

    <x-input.textarea caption="上記以外に実施した授業や事務作業" id="office_work" :rules=$rules />

    <x-input.textarea caption="その他特記事項（テキストの購入・イレギュラーな交通費変更等）" id="other" :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop