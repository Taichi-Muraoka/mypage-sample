@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', '面談日程登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="conference_accept_id" :editData=$editData />

    <p>以下の面談日程希望をもとに、面談日程の登録を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>{{$campus_name}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$student_name}}</td>
        </tr>
        <tr>
            <th>第１希望日時</th>
            <td>{{$formatter::formatYmdDay($conference_date1)}} {{$start_time1}}</td>
        </tr>
        <tr>
            <th>第２希望日時</th>
            <td>
                @if ($conference_date2 != null)
                    {{$formatter::formatYmdDay($conference_date2)}}
                    {{$start_time2}}
                @endif
            </td>
        </tr>
        <tr>
            <th>第３希望日時</th>
            <td>
                @if ($conference_date3 != null)
                    {{$formatter::formatYmdDay($conference_date3)}}
                    {{$start_time3}}
                @endif
            </td>
        </tr>
        <tr>
            <th>特記事項</th>
            <td class="nl2br">{{$comment}}</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="面談日" id="target_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData/>

    <x-input.select caption="ブース" id="booth_cd" :select2=true :mastrData=$booths :editData="$editData" :select2Search=false/>

    <x-input.textarea caption="管理者メモ" id="memo" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData/>
    <x-input.hidden id="student_id" :editData=$editData/>
    <x-input.hidden id="conference_id" :editData=$editData/>

    <x-bs.callout title="登録の際の注意事項" type="warning">
        登録ボタンを押下すると、指定した日時で面談スケジュールが登録されます。<br>
        生徒へお知らせの通知とメールが送信されます。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-edit caption="登録" isIcon=true/>
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop