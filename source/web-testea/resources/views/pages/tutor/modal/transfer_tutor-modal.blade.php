@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th>申請日</th>
        <td>2023/01/10</td>
    </tr>
    <tr>
        <th>申請者種別</th>
        <td>生徒</td>
    </tr>
    <tr>
        <th>授業日・時限</th>
        <td>2023/01/30 4限 15:00</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>コース</th>
        <td>個別指導コース</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>振替希望日時１</th>
        <td>2023/02/03 5限</td>
    </tr>
    <tr>
        <th>振替希望日時２</th>
        <td>2023/02/04 6限</td>
    </tr>
    <tr>
        <th>振替希望日時３</th>
        <td>2023/02/06 5限</td>
    </tr>
    <tr>
        <th>振替理由</th>
        <td>学校行事のため</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>承認待ち</td>
    </tr>
    <tr>
        <th>振替日時（確定）</th>
        <td></td>
    </tr>
</x-bs.table>

@overwrite