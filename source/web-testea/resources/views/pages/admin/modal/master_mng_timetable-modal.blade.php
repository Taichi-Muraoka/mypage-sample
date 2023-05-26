@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>時間割ID</th>
        <td>001</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>時限</th>
        <td>3限</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>15:00</td>
    </tr>
    <tr>
        <th>終了時刻</th>
        <td>16:30</td>
    </tr>
    <tr>
        <th>特別期間</th>
        <td></td>
    </tr>

</x-bs.table>

@overwrite