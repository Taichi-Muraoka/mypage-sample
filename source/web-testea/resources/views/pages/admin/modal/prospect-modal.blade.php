@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>問い合わせ項目</th>
        <td>無料体験授業</td>
    </tr>
    <tr>
        <th>希望校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>学年</th>
        <td>中学２年</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>問い合わせ内容</th>
        <td>数学を受講したい。</td>
    </tr>

</x-bs.table>

@overwrite