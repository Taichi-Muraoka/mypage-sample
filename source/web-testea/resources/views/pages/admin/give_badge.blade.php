@extends('adminlte::page')

@section('title', 'バッジ付与一覧')

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

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="badge_type" caption="バッジ種別" :select2=true >
                <option value="1">紹介</option>
                <option value="2">通塾</option>
                <option value="3">成績</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">認定日</th>
            <th width="10%">バッジ種別</th>
            <th width="10%">校舎</th>
            <th width="15%">生徒名</th>
            <th width="15%">担当者名</th>
            <th>認定理由</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/05/10</td>
            <td>紹介</td>
            <td>久我山</td>
            <td>CWテスト生徒１</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（佐藤次郎さん）</td>
        </tr>
        <tr>
            <td>2023/04/01</td>
            <td>通塾</td>
            <td>久我山</td>
            <td>CWテスト生徒１</td>
            <td>鈴木　花子</td>
            <td>契約期間が３年を超えた</td>
        </tr>
        <tr>
            <td>2022/03/20</td>
            <td>紹介</td>
            <td>久我山</td>
            <td>CWテスト生徒２</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（仙台太郎さん）</td>
        </tr>
        <tr>
            <td>2022/02/20</td>
            <td>成績</td>
            <td>久我山</td>
            <td>CWテスト生徒２</td>
            <td>鈴木　花子</td>
            <td>成績UP</td>
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