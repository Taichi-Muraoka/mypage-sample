@extends('adminlte::page')

@section('title', '授業欠席登録'))

{{-- 子ページ --}}
@section('child_page', true)
@section('base_page_title', 'カレンダー')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="roomcd" :editData=$editData />
    <x-input.hidden id="schedule_id" :editData=$editData />

    <p>１対多授業の欠席登録を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>ブース</th>
            <td>Aテーブル</td>
        </tr>
        <tr>
            <th>コース名</th>
            <td>集団授業</td>
        </tr>
        <tr>
            <th>授業日・時限</th>
            <td>2023/05/25 5限</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false >
        <x-slot name="thead">
          <td>受講生徒</td>
          <td>出欠ステータス</td>
        </x-slot>

        <tr>
            <td id="student_1">CWテスト生徒１</td>
            <td>
                <x-input.select id="absent_1" :select2=true :select2Search=false :editData="$editData">
                    <option value="0" selected>実施前・出席</option>
                    <option value="5">欠席（集団授業）</option>
                </x-input.select>
            </td>
        </tr>
        <tr>
            <td id="student_2">CWテスト生徒２</td>
            <td>
                <x-input.select id="absent_2" :select2=true :select2Search=false :editData="$editData">
                    <option value="0" selected>実施前・出席</option>
                    <option value="5">欠席（集団授業）</option>
                </x-input.select>
            </td>
        </tr>
      </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
            <x-button.back url="{{ route('room_calendar') }}" />

            @if (request()->routeIs('room_calendar-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>


</x-bs.card>



@stop