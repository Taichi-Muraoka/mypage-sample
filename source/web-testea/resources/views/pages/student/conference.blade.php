@extends('adminlte::page')

@section('title', '面談日程連絡')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>面談日程連絡を行います。希望の面談日時を第３希望まで入力してください。</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=true />

    {{-- 第１希望日 --}}
    <x-bs.card>
        <x-input.date-picker caption="第１希望日" id="conference_date1" :editData=$editData />

        <x-input.time-picker caption="開始時刻" id="start_time1" :rules=$rules :editData=$editData />
    </x-bs.card>

    {{-- 第２希望日 --}}
    <x-bs.card>
        <x-input.date-picker caption="第２希望日" id="conference_date2" :editData=$editData />

        <x-input.time-picker caption="開始時刻" id="start_time2" :rules=$rules :editData=$editData />
    </x-bs.card>

    {{-- 第３希望日 --}}
    <x-bs.card>
        <x-input.date-picker caption="第３希望日" id="conference_date3" :editData=$editData />

        <x-input.time-picker caption="開始時刻" id="start_time3" :rules=$rules :editData=$editData />
    </x-bs.card>

    <p>その他特記事項などありましたらご記載ください。</p>
    <x-input.textarea caption="" id="comment" :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop