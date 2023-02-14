@extends('adminlte::page')

@section('title', '振替授業調整一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="student_name" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text caption="教師名" id="teacher_name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="approval_state" caption="承認ステータス" :select2=true :mastrData=$states />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="secretariat_state" caption="事務局ステータス" :select2=true :mastrData=$states />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>申請者種別</th>
            <th>教室</th>
            <th>授業日時</th>
            <th>生徒名</th>
            <th>教師名</th>
            <th>承認ステータス</th>
            <th>事務局ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/16</td>
            <td>生徒</td>
            <td>久我山</td>
            <td>2023/01/30 4限</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>承認</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_regist-edit', 1) }}"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/17</td>
            <td>教師</td>
            <td>久我山</td>
            <td>2023/01/31 4限</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>承認待ち</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_regist-edit', 1) }}"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.transfer_regist-modal')

@stop