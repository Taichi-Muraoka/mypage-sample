@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="70%">体験授業生徒</th>
        <th>会員ステータス</th>
    </tr>
    <tr>
        <td>CWテスト生徒１０</td>
        <td>見込客</td>
    </tr>
    <tr>
        <td>CWテスト生徒１１</td>
        <td>入会</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite