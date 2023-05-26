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

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">講師No</th>
            <th width="15%">講師名</th>
            <th width="15%">日付</th>
            <th width="15%">合計時間</th>
            <th width="15%">超過労働</th>
            <th width="15%">深夜</th>
            <th width="15%">深夜超過労働</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>101</td>
            <td>CWテスト教師１０１</td>
            <td>2023/04/10</td>
            <td>04時間 30分</td>
            <td>00時間 00分</td>
            <td>00時間 30分</td>
            <td>00時間 00分</td>
        </tr>
        <tr>
            <td>102</td>
            <td>CWテスト教師１０２</td>
            <td>2023/04/10</td>
            <td>10時間 00分</td>
            <td>02時間 00分</td>
            <td>00時間 00分</td>
            <td>00時間 00分</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop