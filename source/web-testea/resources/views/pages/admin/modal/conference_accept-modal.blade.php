@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>教室</th>
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

</x-bs.table>

@overwrite