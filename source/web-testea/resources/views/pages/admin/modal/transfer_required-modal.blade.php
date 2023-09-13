@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th>授業日時</th>
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
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>教科</th>
        <td>数学</td>
    </tr>
    <tr>
        <th>出欠ステータス</th>
        <td>未振替</td>
    </tr>


</x-bs.table>

@overwrite

@section('modal-button')

{{-- 打ち合わせのみ更新ボタンを表示 --}}
<x-button.edit href="{{ route('transfer_check-new') }}" icon="" caption="振替情報登録" />

@overwrite