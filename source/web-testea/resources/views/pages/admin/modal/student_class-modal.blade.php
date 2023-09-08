@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th width="35%">ブース</th>
        <td>Aテーブル</td>
    </tr>
    <tr>
        <th width="35%">コース</th>
        <td>個別指導コース</td>
    </tr>
    <tr>
        <th>授業区分</th>
        <td>追加</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>2023/02/28</td>
    </tr>
    <tr>
        <th>時限</th>
        <td>5時限</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>16:00</td>
    </tr>
    <tr>
        <th>終了時刻</th>
        <td>17:30</td>
    </tr>
    <tr>
        <th>講師名/担当者名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒５</td>
    </tr>
    <tr>
        <th>科目</th>
        <td>英語</td>
    </tr>
    <tr>
        <th>通塾</th>
        <td>生徒通塾 - 教師通塾</td>
    </tr>
    <tr>
        <th>授業代講</th>
        <td>なし</td>
    </tr>
    <tr>
        <th>出欠ステータス</th>
        <td>未実施・出席</td>
    </tr>
    <tr>
        <th>授業報告書ステータス</th>
        <td>〇</td>
    </tr>
    <tr>
        <th>振替元授業日・時限</th>
        <td>2023/02/28 4限</td>
    </tr>
    <tr>
        <th>メモ</th>
        <td>メモメモメモメモ</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite