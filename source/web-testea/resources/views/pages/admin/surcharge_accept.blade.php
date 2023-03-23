@extends('adminlte::page')

@section('title', '追加請求申請受付一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="teacher" caption="講師名" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="kinds" caption="請求種別" :select2=true>
                <option value="1">事務作業</option>
                <option value="2">その他費用</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="status" caption="ステータス" :select2=true>
                <option value="1">承認待ち</option>
                <option value="2">承認</option>
                <option value="3">差戻し</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="payment" caption="支払い状況" :select2=true>
                <option value="1">未処理</option>
                <option value="2">処理済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 From" id="holiday_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 To" id="holiday_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>講師名</th>
            <th>請求種別</th>
            <th>時間</th>
            <th>費用</th>
            <th>ステータス</th>
            <th>支払い状況</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10</td>
            <td>CWテスト教師１０１</td>
            <td>事務作業</td>
            <td>60</td>
            <td>1000</td>
            <td>承認</td>
            <td>未処理</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('surcharge_accept-edit', 1) }}" caption="承認"/>
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.surcharge_accept-modal')

@stop