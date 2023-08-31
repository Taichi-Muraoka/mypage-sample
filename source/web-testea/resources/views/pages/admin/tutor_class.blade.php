@extends('adminlte::page')

@section('title', '講師授業集計')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="roomcd" caption="校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
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
            <th width="5%">個別</th>
            <th width="6%">１対２</th>
            <th width="6%">１対３</th>
            <th width="5%">集団</th>
            <th width="5%">家庭教師</th>
            <th width="5%">演習</th>
            <th width="5%">ハイプラン</th>
            <th width="5%">代講(受)</th>
            <th width="5%">緊急代講(受)</th>
            <th width="5%">代講(出)</th>
            <th width="5%">緊急代講(出)</th>
            <th width="5%">初回体験授業</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>100101</td>
            <td>CWテスト教師１０１</td>
            <td class="t-price">18</td>
            <td class="t-price">3</td>
            <td class="t-price">4.5</td>
            <td class="t-price">3</td>
            <td class="t-price">6</td>
            <td class="t-price">2</td>
            <td class="t-price">10</td>
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
            <td class="t-price">18</td>
            <td class="t-price">3</td>
            <td class="t-price">4.5</td>
            <td class="t-price">3</td>
            <td class="t-price">6</td>
            <td class="t-price">2</td>
            <td class="t-price">10</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td class="t-price">0</td>
            <td>
                <x-button.list-dtl/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.tutor_class-modal')

@stop