@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', '振替情報登録（未振替授業）')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 親ページを指定(要振替授業一覧) --}}
@section('base_page', route('transfer_required'))
@section('base_page_title', '要振替授業一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>個別指導授業の振替スケジュール登録を行います。</p>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData />
    <x-input.hidden id="student_id" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />
    <x-input.hidden id="period_no_bef" />

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>生徒名</th>
            <td>{{$student_name}}</td>
        </tr>
        <tr>
            <th>授業日・時限</th>
            <td>{{$formatter::formatYmdDay($target_date)}} {{$period_no}}限</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$campus_name}}</td>
        </tr>
        <tr>
            <th>コース</th>
            <td>{{$course_name}}</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>{{$tutor_name}}</td>
        </tr>
        <tr>
            <th>教科</th>
            <td>{{$subject_name}}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.card>
        <x-input.date-picker caption="振替日" id="target_date" :rules=$rules/>

        <x-input.select caption="時限" id="period_no"
            :select2=true :select2Search=false :blank=true>
            {{-- vueで動的にプルダウンを作成 --}}
            <option v-for="item in selectGetItemPeriods" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻（変更する場合）" id="start_time" :rules=$rules />

    </x-bs.card>

    <x-input.select caption="講師名（変更する場合）" id="change_tid" :select2=true :mastrData=$tutors
        :select2Search=true />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        入力した振替授業のスケジュールが登録されます。<br>
        対象の生徒・講師へお知らせの通知とメールが送信されます。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back url="{{route('transfer_required')}}" />

            {{-- 登録時 --}}
            <x-button.submit-new isIcon=true />

        </div>
    </x-slot>

</x-bs.card>

@stop
