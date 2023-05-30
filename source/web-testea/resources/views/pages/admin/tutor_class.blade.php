@extends('adminlte::page')

@section('title', '講師授業集計')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="holiday_date_from"/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="holiday_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list :mock=true>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">講師ID</th>
            <th width="20%">講師名</th>
            <th width="10%">実施授業時間（分）</th>
            <th width="10%">代講授業数（受）</th>
            <th width="10%">緊急代講授業数（受）</th>
            <th width="10%">代講授業数（出）</th>
            <th width="10%">緊急代講授業数（出）</th>
            <th width="10%">初回授業数</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>100101</td>
            <td>CWテスト教師１０１</td>
            <td class="t-price">1,980</td>
            <td class="t-price">0</td>
            <td class="t-price">1</td>
            <td class="t-price">1</td>
            <td class="t-price">0</td>
            <td class="t-price">2</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>100102</td>
            <td>CWテスト教師１０２</td>
            <td class="t-price">1,080</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td>
                <x-button.list-dtl  disabled/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.tutor_class-modal')

@stop