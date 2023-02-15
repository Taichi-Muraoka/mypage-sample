@extends('pages.common.modal')

@section('modal-body')

<p>
    以下の授業を「出席」（授業実施）として登録します。<br>
    よろしいですか？
</p>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">授業日・時限</th>
        <td>2023/02/13 6限</td>
    </tr>
    <tr>
        <th>教室</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>授業スペース</th>
        <td>第一校舎３テーブル</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒２</td>
    </tr>
</x-bs.table>

@overwrite