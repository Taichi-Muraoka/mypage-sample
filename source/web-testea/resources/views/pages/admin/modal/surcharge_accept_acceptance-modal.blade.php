@extends('pages.common.modal')

@section('modal-body')

<p>以下の追加請求申請を受付し、以下の処理を行います。<br>
    よろしいですか？</p>

<ul>
    <li>ステータスを「承認」に変更</li>
    <li>支払年月を設定</li>
</ul>

<x-bs.table :hover=false :vHeader=true>
    {{-- モック用処理 --}}
    <tr width="35%">
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>請求種別</th>
        <td>経費</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>実施日</th>
        <td>2023/01/09</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>2000</td>
    </tr>
    <tr>
        <th>支払年月</th>
        <td>2023/02</td>
    </tr>

    {{-- 本番用処理 --}}
    {{-- <tr>
        <th width="35%">生徒名</th>
        <td>@{{item.sname}}</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>@{{item.tname}}</td>
    </tr> --}}
</x-bs.table>


@overwrite