@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>申請日</th>
        <td>2023/01/16</td>
    </tr>
    <tr>
        <th>申請者種別</th>
        <td>生徒</td>
    </tr>
    <tr>
        <th>授業日・時限</th>
        <td>2023/01/30 4限</td>
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
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>教科</th>
        <td>数学</td>
    </tr>
    <tr>
        <th>振替候補日・時限１</th>
        <td>2023/02/03 5限</td>
    </tr>
    <tr>
        <th>振替候補日・時限２</th>
        <td>2023/02/04 6限</td>
    </tr>
    <tr>
        <th>振替候補日・時限３</th>
        <td>2023/02/06 5限</td>
    </tr>
    <tr>
        <th>振替理由／連絡事項など</th>
        <td>学校行事のため</td>
    </tr>
    <tr>
        <th>ステータス</th>
        {{-- <td>承認待ち</td> --}}
        <td>管理者対応済</td>
    </tr>
    <tr>
        <th>振替代講区分</th>
        {{-- <td></td> --}}
        <td>振替</td>
        {{-- <td>代講</td> --}}
    </tr>
    <tr>
        <th>振替日・時限（確定）</th>
        <td>2023/02/06 5限</td>
        {{-- <td></td> --}}
    </tr>
    {{-- <tr>
        <th>代講講師名</th>
        <td>教師１０５</td>
    </tr> --}}

</x-bs.table>

@overwrite