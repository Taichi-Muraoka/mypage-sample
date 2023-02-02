@extends('pages.common.modal')

@section('modal-body')

<p>
    以下のコース変更・授業追加申請を受付し、申請者に受付メッセージを自動送信します。<br>
    よろしいですか？
</p>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">申請日</th>
        <td>@{{item.apply_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>追加変更種別</th>
        <td>@{{item.course_name}}</td>
    </tr>
</x-bs.table>

@overwrite