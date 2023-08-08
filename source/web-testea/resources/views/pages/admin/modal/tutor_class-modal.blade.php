@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="70%">体験授業生徒</th>
        <th>体験授業日</th>
        <th>会員ステータス</th>
        <th>入会日</th>
    </tr>
    <tr>
        <td>CWテスト生徒１０</td>
        <td>2023/06/17</td>
        <td>見込客</td>
        <td></td>
    </tr>
    <tr>
        <td>CWテスト生徒１１</td>
        <td>2023/06/10</td>
        <td>入会</td>
        <td>2023/06/25</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite