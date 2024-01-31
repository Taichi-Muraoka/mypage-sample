@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', '授業欠席登録')

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$schedule />
    <x-input.hidden id="schedule_id" :editData=$schedule />
    <x-input.hidden id="studentCnt" :editData=$schedule :rules=$rules />

    <p>１対多授業の欠席登録を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>{{$schedule->campus_name}}</td>
        </tr>
        <tr>
            <th>ブース</th>
            <td>{{$schedule->booth_name}}</td>
        </tr>
        <tr>
            <th>コース名</th>
            <td>{{$schedule->course_name}}</td>
        </tr>
        <tr>
            <th>授業日・時限</th>
            <td>{{$formatter::formatYmdDay($schedule->target_date)}} {{$schedule->period_no}}限</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>{{$schedule->tutor_name}}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false class="mb-small">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
          <td width="40%">受講生徒</td>
          <td>出欠ステータス</td>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < $schedule['studentCnt']; $i++) <tr>
            {{-- hidden --}}
            <x-input.hidden id="class_member_id_{{$i}}" :editData=$classMembers[$i] />
            <x-input.hidden id="student_id_{{$i}}" :editData=$classMembers[$i] />
            <td>{{$classMembers[$i]['student_name_' . $i]}}</td>
            <td>
                <x-input.select id="absent_status_{{$i}}" :select2=true :select2Search=false :blank=false 
                    :mastrData=$todayabsentList :rules=$rules :editData=$classMembers[$i] />
            </td>
        </tr>
        @endfor
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back url="{{ route('room_calendar') }}" />
            {{-- 編集のみ --}}
            <x-button.submit-edit />
        </div>
    </x-slot>

</x-bs.card>

@stop