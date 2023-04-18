@extends('adminlte::page')

@section('title', '生徒授業情報検索')

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
            <x-input.select id="kinds" caption="コース名" :select2=true>
                <option value="1">個別指導コース</option>
                <option value="4">集団指導</option>
                <option value="5">その他・自習</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="teacher" caption="担当講師名" />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="kinds" caption="授業種別" :select2=true>
                <option value="1">通常</option>
                <option value="2">振替</option>
                <option value="3">初回授業（入会金無料）</option>
                <option value="4">体験授業１回目</option>
                <option value="5">追加</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="kinds" caption="出欠ステータス" :select2=true>
                <option value="1">実施前</option>
                <option value="2">出席</option>
                <option value="3">当日欠席（講師出勤あり）</option>
                <option value="4">当日欠席（講師出勤なし）</option>
                <option value="3">後日振替（振替日未定）</option>
                <option value="4">後日振替（振替日確定）</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="教科" id="subject" :select2=true :select2Search=false>
                <option value="1">国語</option>
                <option value="2">数学</option>
                <option value="3">理科</option>
                <option value="4">社会</option>
                <option value="5">英語</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="holiday_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="holiday_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list :mock=true>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">授業日</th>
            <th>時限</th>
            <th>校舎</th>
            <th>生徒名</th>
            <th>講師名</th>
            <th>コース名</th>
            <th>教科</th>
            <th>授業種別</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/02/28</td>
            <td>6</td>
            <td>久我山</td>
            <td>CWテスト生徒５</td>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>英語</td>
            <td>追加</td>
            <td>実施前</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/02/28</td>
            <td>5</td>
            <td>久我山</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０２</td>
            <td>個別指導コース</td>
            <td>数学</td>
            <td>通常</td>
            <td>実施前</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/02/24</td>
            <td>6</td>
            <td>久我山</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>理科</td>
            <td>体験授業１回目</td>
            <td>当日欠席（講師出勤あり）</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/02/24</td>
            <td>5</td>
            <td>久我山</td>
            <td>CWテスト生徒３</td>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>社会</td>
            <td>通常</td>
            <td>後日振替（振替日未定）</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>2023/02/24</td>
            <td>4</td>
            <td>久我山</td>
            <td>CWテスト生徒４</td>
            <td>CWテスト教師１０２</td>
            <td>個別指導コース</td>
            <td>英語</td>
            <td>振替</td>
            <td>出席</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.student_class-modal')

@stop