@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td>2023/02/20</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>希望内容</th>
        <td>定期テスト対策で来週１コマ追加したい （英語）<br>
            2023/3/1の5限か6限希望</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>未対応</td>
    </tr>
    <tr>
        <th>事務局コメント</th>
        <td class="nl2br"></td>
    </tr>

</x-bs.table>

@overwrite