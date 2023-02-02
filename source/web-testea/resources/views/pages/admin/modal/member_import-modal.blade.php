@extends('pages.common.modal')

@section('modal-body')

<p>
    以下の会員を登録し、会員のメールアドレスに初期パスワードを送信します。<br>
    よろしいですか？
</p>

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">受信日付</th>
        <td>@{{item.date}}</td>
    </tr>
    <tr>
        <th>会員ID</th>
        <td>@{{item.id}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>メールアドレス</th>
        <td>@{{item.mailaddress}}</td>
    </tr>
</x-bs.table>
@overwrite