@extends('adminlte::page')

@section('title', '欠席申請')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>欠席申請を行います。欠席したい授業日・欠席理由を入力してください。</p>
    <x-bs.form-group>
        <x-input.radio caption="個別教室" id="lesson_type-1" name="lesson_type" value="{{ App\Consts\AppConst::CODE_MASTER_8_1 }}" :checked=true
            :editData=$editData />
        <x-input.radio caption="家庭教師" id="lesson_type-2" name="lesson_type" value="{{ App\Consts\AppConst::CODE_MASTER_8_2 }}" :editData=$editData />
    </x-bs.form-group>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 個別教室 --}}
    <x-bs.card vShow="form.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_1 }}">

        {{-- チェンジイベントを取得し、教室と教師を取得する --}}
        <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGet" :mastrData=$scheduleMaster :editData=$editData />

        {{-- 詳細を表示 --}}
        <x-bs.table :hover=false :vHeader=true class="mb-4">
            <tr>
                <th class="t-minimum" width="25%">教室</th>
                <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
            </tr>
            <tr>
                <th>教師</th>
                <td><span v-cloak>@{{selectGetItem.teacher_name}}<span v-if="selectGetItem.teacher_name">先生</span></span></td>
            </tr>
        </x-bs.table>

    </x-bs.card>

    {{-- 家庭教師 --}}
    <x-bs.card vShow="form.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_2 }}">
        <x-input.date-picker caption="授業日" id="lesson_date" :editData=$editData />

        <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

        <x-input.select caption="教師名" id="tid" :select2=true :mastrData=$home_teachers :editData=$editData />
    </x-bs.card>

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