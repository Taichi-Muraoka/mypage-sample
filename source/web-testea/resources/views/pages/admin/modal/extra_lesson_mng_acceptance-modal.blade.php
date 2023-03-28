@extends('pages.common.modal')

@section('modal-body')

<p>
    以下の追加授業申請を受付し、申請者へ受付メッセージを自動送信します。<br>
    よろしいですか？
</p>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td>2023/01/30</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
</x-bs.table>

@overwrite