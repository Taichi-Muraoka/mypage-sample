@extends('adminlte::page')

@section('title', '超過勤務者一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 From" id="date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 To" id="date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>講師No</th>
            <th>講師名</th>
            <th>日付</th>
            <th>開始時間</th>
            <th>終了時間</th>
            <th width="15%">合計時間</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>101</td>
            <td>CWテスト教師１０１</td>
            <td>2023/04/10</td>
            <td>18:00</td>
            <td>22:30</td>
            <td>04時間30分</td>
        </tr>
        <tr>
            <td>102</td>
            <td>CWテスト教師１０２</td>
            <td>2023/04/10</td>
            <td>12:00</td>
            <td>22:00</td>
            <td>10時間00分</td>
        </tr>

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- フッター --}}
    <div class="d-flex justify-content-end">
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </div>

</x-bs.card-list>

@stop