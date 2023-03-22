@extends('adminlte::page')

@section('title', '講師授業検索')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="holiday_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="holiday_date_to" />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="teacher" caption="講師名" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="kinds" caption="コース名" :select2=true>
                <option value="1">個別指導コース</option>
                <option value="4">集団授業</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list :mock=true>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="35%">講師名</th>
            <th width="35%">コース名</th>
            <th>実施授業数</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>12</td>
        </tr>
        <tr>
            <td>CWテスト教師１０２</td>
            <td>集団授業</td>
            <td>8</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.student_class-modal')

@stop