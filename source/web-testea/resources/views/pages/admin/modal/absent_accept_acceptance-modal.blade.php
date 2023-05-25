@extends('pages.common.modal')

@section('modal-body')

<p>以下の欠席申請を受付し、以下の処理を行います。<br>
    よろしいですか？</p>

<ul>
    <li>生徒への受付メッセージ自動送信</li>
    <li>担当講師への通知メッセージ自動送信</li>
    <li>担当講師へのメール通知</li>
</ul>

<x-bs.table :hover=false :vHeader=true>
    {{-- モック用処理 --}}
    <tr>
        <th width="35%">生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>2023/05/22 16:00</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
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