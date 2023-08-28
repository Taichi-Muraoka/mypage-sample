@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true  vShow="item.id == 1">
    <tr>
        <th>請求種別</th>
        <td>業務依頼（本部）</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>実施日</th>
        <td>2023/01/10</td>
    </tr>
    <tr>
        <th>開始時刻</th>
        <td>16:00</td>
    </tr>
    <tr>
        <th>時間（分）</th>
        <td>60</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>1000</td>
    </tr>
    <tr>
        <th>内容（作業・費目等）</th>
        <td>教材プリントコピー作業</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>承認</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td></td>
    </tr>

    {{-- ステータスが「承認」時のみ以下表示 --}}
    <tr>
        <th>支払年月</th>
        <td>2023/03</td>
    </tr>
    <tr>
        <th>支払状況</th>
        <td>未処理</td>
    </tr>
</x-bs.table>

<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true  vShow="item.id == 2">
    <tr>
        <th>請求種別</th>
        <td>経費</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>2000</td>
    </tr>
    <tr>
        <th>内容（作業・費目等）</th>
        <td>テキスト購入</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>差戻し</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td>再申請してください</td>
    </tr>

    {{-- ステータスが「承認」時のみ以下表示 --}}
    <tr>
        <th>支払年月</th>
        <td></td>
    </tr>
    <tr>
        <th>支払状況</th>
        <td></td>
    </tr>
</x-bs.table>

@overwrite