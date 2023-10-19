@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>第１希望日時</th>
        <td>2023/01/30 16:00</td>
    </tr>
    <tr>
        <th>第２希望日時</th>
        <td>2023/01/31 16:00</td>
    </tr>
    <tr>
        <th>第３希望日時</th>
        <td>2023/02/01 16:00</td>
    </tr>
    <tr>
        <th>特記事項</th>
        <td></td>
    </tr>
    <tr>
        <th>面談日</th>
        <td>2023/01/30</td>
    </tr>
    <tr>
        <th>面談担当者</th>
        <td>久我山教室長</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>16:00</td>
    </tr>
    <tr>
        <th>管理者メモ</th>
        <td>前日に確認の連絡をすること</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>登録済</td>
    </tr>

</x-bs.table>

@overwrite