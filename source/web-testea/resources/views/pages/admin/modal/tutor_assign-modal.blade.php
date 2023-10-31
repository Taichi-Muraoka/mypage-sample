@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th width="35%">講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th width="35%">講師電話番号</th>
        <td>070-1111-2222</td>
    </tr>
    <tr>
        <th width="35%">講師メールアドレス</th>
        <td><a href="mailto:teacher0101@mp-sample.rulez.jp">teacher0101@mp-sample.rulez.jp</a></td>
    </tr>
    <tr>
        <th width="35%">性別</th>
        <td>男性</td>
    </tr>
    <tr>
        <th width="35%">在籍大学</th>
        <td>青山学院大学</td>
    </tr>
    <tr>
        <th width="35%">出身高校</th>
        <td>青山学院高等部</td>
    </tr>
    <tr>
        <th width="35%">出身中学</th>
        <td>成城学園中等部</td>
    </tr>
    <tr>
        <th width="35%">曜日</th>
        <td>月曜</td>
    </tr>
    <tr>
        <th width="35%">時限</th>
        <td>3限</td>
    </tr>
    <tr>
        <th>担当科目</th>
        <td>国語,数学,英語</td>
    </tr>
</x-bs.table>

@overwrite

@section('modal-button')

@overwrite