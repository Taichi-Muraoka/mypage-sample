@extends('adminlte::page')

@section('title', '空き講師検索')

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
            <x-input.text id="teacher" caption="講師名" />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="" caption="曜日" :select2=true>
                <option value="1">月曜</option>
                <option value="2">火曜</option>
                <option value="3">水曜</option>
                <option value="4">木曜</option>
                <option value="5">金曜</option>
                <option value="6">土曜</option>
                <option value="7">日曜</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="" caption="時限" :select2=true>
                <option value="1">1限</option>
                <option value="2">2限</option>
                <option value="3">3限</option>
                <option value="4">4限</option>
                <option value="5">5限</option>
                <option value="6">6限</option>
                <option value="7">7限</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="subject" caption="担当科目" :select2=true>
                <option value="1">国語</option>
                <option value="2">数学</option>
                <option value="3">理科</option>
                <option value="4">社会</option>
                <option value="5">英語</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list :mock=true>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>校舎</th>
            <th>講師名</th>
            <th>曜日</th>
            <th>時限</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>久我山</td>
            <td>CWテスト教師１０１</td>
            <td>月曜</td>
            <td>3限</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>久我山</td>
            <td>CWテスト教師１０１</td>
            <td>火曜</td>
            <td>4限</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>久我山</td>
            <td>CWテスト教師１０２</td>
            <td>水曜</td>
            <td>5限</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>久我山</td>
            <td>CWテスト教師１０２</td>
            <td>木曜</td>
            <td>6限</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>


    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.tutor_assign-modal')

@stop