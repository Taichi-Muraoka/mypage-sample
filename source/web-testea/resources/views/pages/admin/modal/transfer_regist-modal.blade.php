@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>申請日</th>
        <td>2023/01/16</td>
    </tr>
    <tr>
        <th>申請者種別</th>
        <td>生徒</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>2023/01/30 4限</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>教師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>承認ステータス</th>
        <td>承認待ち</td>
    </tr>
    <tr>
        <th>事務局ステータス</th>
        <td>未対応</td>
    </tr>

</x-bs.table>

@overwrite