@extends('adminlte::page')

@section('title', '授業情報検索')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="roomcd" caption="校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="student" caption="生徒名" />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="course_cd" caption="コース名" :select2=true>
                <option value="10100">個別指導コース</option>
                <option value="10200">1対2コース</option>
                <option value="10300">1対3コース</option>
                <option value="10400">家庭教師</option>
                <option value="10500">演習</option>
                <option value="10600">ハイプラン</option>
                <option value="20100">集団指導</option>
                <option value="90100">その他・自習</option>
                <option value="90200">面談</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="teacher" caption="講師名" />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="lesson_kind" caption="授業区分" :select2=true>
                <option value="1">通常授業</option>
                <option value="2">特別期間講習</option>
                <option value="3">追加授業</option>
                <option value="4">初回授業</option>
                <option value="5">体験授業１回目</option>
                <option value="6">体験授業２回目</option>
                <option value="7">体験授業３回目</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="absent_status" caption="出欠ステータス" :select2=true>
                <option value="1">実施前・出席</option>
                <option value="2">当日欠席（講師出勤あり）</option>
                <option value="3">当日欠席（講師出勤なし）</option>
                <option value="4">未振替</option>
                <option value="5">振替中</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="教科" id="subject_cd" :select2=true :select2Search=false>
                <option value="1">国語</option>
                <option value="2">数学</option>
                <option value="3">理科</option>
                <option value="4">社会</option>
                <option value="5">英語</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="授業報告書ステータス" id="report" :select2=true :select2Search=false>
                <option value="1">―（登録不要）</option>
                <option value="2">✕（要登録・差戻し）</option>
                <option value="3">△（承認待ち）</option>
                <option value="4">〇（登録済み）</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="日付 From" id="holiday_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="日付 To" id="holiday_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list :mock=true>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">日付</th>
            <th>曜日</th>
            <th>時限/開始</th>
            <th>コース</th>
            <th>校舎</th>
            <th>生徒名</th>
            <th>講師名/担当者名</th>
            <th>教科</th>
            <th>授業区分</th>
            <th>出欠</th>
            <th>報告書</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/09/28</td>
            <td>木</td>
            <td>6限</td>
            <td>個別</td>
            <td>久</td>
            <td>CWテスト生徒５</td>
            <td>CWテスト教師１０１</td>
            <td>英</td>
            <td>追加</td>
            <td>前・出席</td>
            <td>〇</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/09/28</td>
            <td>木</td>
            <td>5限</td>
            <td>個別</td>
            <td>久</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０２</td>
            <td>数</td>
            <td>通常・振替</td>
            <td>前・出席</td>
            <td>✕</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/09/24</td>
            <td>日</td>
            <td>6現</td>
            <td>個別</td>
            <td>久</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>理</td>
            <td>体験１</td>
            <td>当欠(出勤)</td>
            <td>〇</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/09/24</td>
            <td>日</td>
            <td>5限</td>
            <td>個別</td>
            <td>久</td>
            <td>CWテスト生徒３</td>
            <td>CWテスト教師１０１</td>
            <td>社</td>
            <td>通常</td>
            <td>未振替</td>
            <td>－</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/09/24</td>
            <td>日</td>
            <td>4限</td>
            <td>個別</td>
            <td>久</td>
            <td>CWテスト生徒４</td>
            <td>CWテスト教師１０２</td>
            <td>国英数</td>
            <td>追加</td>
            <td>前・出席</td>
            <td>〇</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/02/24</td>
            <td>日</td>
            <td>15:00</td>
            <td>面談</td>
            <td>久</td>
            <td>CWテスト生徒2</td>
            <td>久我山教室長</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.student_class-modal')

@stop