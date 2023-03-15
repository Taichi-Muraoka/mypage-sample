@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th>登録日時</th>
        <td>2023/01/10 17:00</td>
    </tr>
    <tr>
        <th>申請者種別</th>
        <td>講師</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>2023/01/30 4限 15:00</td>
    </tr>
    <tr>
        <th>振替希望日時</th>
        <td>2023/02/06 4限 15:00</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>教師１</td>
    </tr>
    <tr>
        <th>振替理由</th>
        <td>私用都合のため</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>承認待ち</td>
    </tr>
</x-bs.table>

@overwrite