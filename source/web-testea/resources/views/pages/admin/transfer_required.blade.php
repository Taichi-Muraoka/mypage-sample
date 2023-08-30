@extends('adminlte::page')

@section('title', '要振替授業一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="kinds" caption="校舎" :select2=true>
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="生徒名" id="student_name" :select2=true :editData=$editData>
                <option value="1">CWテスト生徒１</option>
                <option value="2">CWテスト生徒２</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="講師名" id="teacher_name" :select2=true :editData=$editData>
                <option value="1">CWテスト講師１</option>
                <option value="2">CWテスト講師２</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>校舎</th>
            <th>授業日</th>
            <th>生徒名</th>
            <th>講師名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>久我山</td>
            <td>2023/01/30</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
        <tr>
            <td>久我山</td>
            <td>2023/01/31</td>
            <td>CWテスト生徒２</td>
            <td>CWテスト教師１０２</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.transfer_required-modal')

@stop