@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th>担当可能な講師一覧</th>
        <td>CWテスト講師１０１<br>
CWテスト講師１０１<br>
CWテスト講師１０２<br>
CWテスト講師１０３<br>
CWテスト講師１０４<br>
CWテスト講師１０５<br>
        </td>
    </tr>
</x-bs.table>

@overwrite