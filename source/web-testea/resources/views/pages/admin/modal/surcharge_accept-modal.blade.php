@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>請求種別</th>
        <td>事務作業</td>
    </tr>

    {{-- 種別：事務作業の場合 --}}
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>実施日</th>
        <td>2023/03/20</td>
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
        <th>作業内容</th>
        <td>教材プリントコピー作業</td>
    </tr>

    {{-- 種別：その他費用の場合 --}}
    {{-- <tr>
        <th>費目</th>
        <td>テキスト購入</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>1000</td>
    </tr> --}}

    {{-- 共通 --}}
    <tr>
        <th>ステータス</th>
        <td>承認</td>
    </tr>
    <tr>
        <th>事務局コメント</th>
        <td></td>
    </tr>

    {{-- ステータスが「承認」時のみ以下表示 --}}
    <tr>
        <th>支払い状況</th>
        <td>未処理</td>
    </tr>
    <tr>
        <th>支払年月</th>
        <td>2023/04</td>
    </tr>
</x-bs.table>

@overwrite