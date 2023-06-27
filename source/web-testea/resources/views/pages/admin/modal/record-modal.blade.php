@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">登録日時</th>
        <td>2023/01/10 17:00</td>
    </tr>
    <tr>
        <th>担当者名</th>
        <td>教室管理者（仙台駅前）</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>記録種別</th>
        <td>面談記録</td>
    </tr>
    <tr>
        <th>内容</th>
        <td class="nl2br">志望校について
志望校の確認と今後の授業の進め方についてお話しした。
本人の志望校：XXXX高校
今後は本人が苦手な英語を中心に授業を進める方針とする。
        </td>
    </tr>

</x-bs.table>

@overwrite