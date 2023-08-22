@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th>登録日</th>
        <td>2023/05/15</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th width="35%">授業日・時限</th>
        <td>2023/05/15 4限</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>コース</th>
        <td>個別指導</td>
    </tr>
    <tr>
        <th>科目</th>
        <td>数学</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>今月の目標</th>
        <td>正負の数の計算をマスターする</td>
    </tr>
    <tr>
        <th>授業教材１</th>
        <td>中１数学ドリル演習 p13-18</td>
    </tr>
    <tr>
        <th>授業単元１</th>
        <td>正負の数・乗法と除法<br>
            正負の数・四則の混じった計算<br>
            正負の数・その他（単元まとめ）
        </td>
    </tr>
    <tr>
        <th>授業教材２</th>
        <td></td>
    </tr>
    <tr>
        <th>授業単元２</th>
        <td></td>
    </tr>
    <tr>
        <th>確認テスト内容</th>
        <td>数学ドリル p19</td>
    </tr>
    <tr>
        <th>確認テスト得点</th>
        <td>10/10点</td>
    </tr>
    <tr>
        <th>宿題達成度</th>
        <td>100%</td>
    </tr>
    <tr>
        <th>達成・課題点</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">よく理解できています</td>
    </tr>
    <tr>
        <th>解決策</th>
        <td class="nl2br"></td>
    </tr>
    <tr>
        <th>その他</th>
        <td class="nl2br"></td>
    </tr>
    <tr>
        <th>宿題教材１</th>
        <td>中１数学ドリル演習 p19-20</td>
    </tr>
    <tr>
        <th>宿題単元１</th>
        <td>
            正負の数・その他（単元まとめ）
        </td>
    </tr>
    <tr>
        <th>宿題教材２</th>
        <td></td>
    </tr>
    <tr>
        <th>宿題単元２</th>
        <td></td>
    </tr>
    <tr>
        <th>承認ステータス</th>
        <td class="nl2br">承認待ち</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td class="nl2br"></td>
    </tr>
</x-bs.table>


@overwrite