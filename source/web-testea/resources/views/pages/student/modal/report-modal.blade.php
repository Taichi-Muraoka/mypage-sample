@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
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
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>今月の目標</th>
        <td>連立方程式の習得</td>
    </tr>
    <tr>
        <th>授業教材・単元１</th>
        <td>数学ドリル p13-18・連立方程式</td>
    </tr>
    <tr>
        <th>授業教材・単元２</th>
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
        <th>宿題教材・単元１</th>
        <td>数学ドリル p20・連立方程式</td>
    </tr>
    <tr>
        <th>宿題教材・単元２</th>
        <td></td>
    </tr>

    {{-- 元の項目 --}}
    {{-- <tr>
        <th width="35%">授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>コース</th>
        <td></td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>@{{item.tname}}</td>
    </tr>
    <tr>
        <th>学習内容</th>
        <td class="nl2br">@{{item.content}}</td>
    </tr>
    <tr>
        <th>次回までの宿題</th>
        <td class="nl2br">@{{item.homework}}</td>
    </tr>
    <tr>
        <th>講師よりコメント</th>
        <td class="nl2br">@{{item.teacher_comment}}</td>
    </tr> --}}
</x-bs.table>

@overwrite