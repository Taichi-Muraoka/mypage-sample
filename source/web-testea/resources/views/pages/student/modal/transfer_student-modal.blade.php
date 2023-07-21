@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th>申請日</th>
        <td>2023/01/10</td>
    </tr>
    <tr>
        <th>申請者種別</th>
        <td>講師</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>2023/01/30 5限 16:00</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>教師１０１</td>
    </tr>
    <tr>
        <th>振替希望日時１</th>
        <td>2023/02/03 5限 16:00</td>
    </tr>
    <tr>
        <th>振替希望日時２</th>
        <td>2023/02/04 6限 17:30</td>
    </tr>
    <tr>
        <th>振替希望日時３</th>
        <td>2023/02/06 5限 16:00</td>
    </tr>
    <tr>
        <th>振替理由／連絡事項など</th>
        <td>私用都合のため</td>
    </tr>
    <tr>
        <th>ステータス</th>
        {{-- <td>承認待ち</td> --}}
        <td>管理者対応済</td>
    </tr>
    <tr>
        <th>振替日時（確定）</th>
        <td>2023/02/04 6限 17:30</td>
    </tr>
    <tr>
        <th>代講講師名</th>
        <td>教師１０５</td>
    </tr>
</x-bs.table>

@overwrite