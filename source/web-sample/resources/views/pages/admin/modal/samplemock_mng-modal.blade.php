@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">登録日</th>
        <td>2025/03/01</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>テスト生徒１</td>
    </tr>
    <tr>
        <th>サンプル件名</th>
        <td>テスト件名１</td>
    </tr>
    <tr>
        <th>サンプルテキスト</th>
        <td class="nl2br">サンプルサンプル</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>未対応</td>
    </tr>
    <tr>
        <th>登録者</th>
        <td>管理者１</td>
    </tr>
</x-bs.table>

@overwrite
