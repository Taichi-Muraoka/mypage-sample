@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>契約コースコード</th>
        <td>101</td>
    </tr>
    <tr>
        <th>授業種別</th>
        <td>個別</td>
    </tr>
    <tr>
        <th>学校区分</th>
        <td>中</td>
    </tr>
    <tr>
        <th>名称</th>
        <td>個別指導 中学生コース（受験準備学年）</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>33,880</td>
    </tr>
    <tr>
        <th>単価</th>
        <td>8,470</td>
    </tr>
    <tr>
        <th>回数</th>
        <td>4</td>
    </tr>

</x-bs.table>

@overwrite