@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>志望順</th>
        <td>1</td>
    </tr>
    <tr>
        <th>受験校</th>
        <td>青山高等学校</td>
    </tr>
    <tr>
        <th>学部・学科名</th>
        <td>普通科</td>
    </tr>
    <tr>
        <th>受験年度</th>
        <td>2022</td>
    </tr>
    <tr>
        <th>受験日程名</th>
        <td>A日程</td>
    </tr>
    <tr>
        <th>受験日</th>
        <td>2023/03/03</td>
    </tr>
    <tr>
        <th>合否</th>
        <td>合格</td>
    </tr>
    <tr>
        <th>備考</th>
        <td class="nl2br"></td>
    </tr>

</x-bs.table>

@overwrite