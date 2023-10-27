@extends('pages.common.modal')

@section('modal-body')

<p>以下の追加請求申請を受付し、以下の処理を行います。<br>
    よろしいですか？</p>

<ul>
    <li>ステータスを「承認」に変更</li>
    <li>支払年月を設定（申請日の翌月に自動設定）</li>
</ul>

{{-- <x-bs.table :hover=false :vHeader=true> --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true  vShow="item.id == 2">
    {{-- モック用処理 --}}
    <tr>
        <th>申請日</th>
        <td>2023/01/09</td>
    </tr>
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
{{-- 種別：事務作業の場合 --}}
    {{-- <tr>
        <th>開始時刻</th>
        <td>16:00</td>
    </tr>
    <tr>
        <th>時間（分）</th>
        <td>60</td>
    </tr> --}}
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
        <td>承認待ち</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td></td>
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
        <td>@{{$filters.formatYmd(item.lesson_date)}} @{{$filters.formatHm(item.start_time)}}</td>
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

<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true  vShow="item.id == 3">
    {{-- モック用処理 --}}
    <tr>
        <th>申請日</th>
        <td>2023/01/05</td>
    </tr>
    <tr width="35%">
        <th>講師名</th>
        <td>CWテスト教師１０３</td>
    </tr>
    <tr>
        <th>請求種別</th>
        <td>業務依頼（教室）</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>西永福</td>
    </tr>
    <tr>
        <th>実施日</th>
        <td>2023/01/04</td>
    </tr>
{{-- 種別：事務作業の場合 --}}
    <tr>
        <th>開始時刻</th>
        <td>17:00</td>
    </tr>
    <tr>
        <th>時間（分）</th>
        <td>90</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>1500</td>
    </tr>
    <tr>
        <th>内容（作業・費目等）</th>
        <td>教材プリントコピー作業</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>承認待ち</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td></td>
    </tr>
    <tr>
        <th>支払年月</th>
        <td>2023/02</td>
    </tr>

</x-bs.table>

@overwrite