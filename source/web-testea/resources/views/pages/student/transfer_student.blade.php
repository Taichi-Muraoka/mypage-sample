@extends('adminlte::page')

@section('title', '振替調整一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_student-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>登録日時</th>
            <th>申請者種別</th>
            <th>授業日・時限</th>
            <th>教師名</th>
            <th>承認ステータス</th>
            <th>事務局ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10 17:00</td>
            <td>教師</td>
            <td>2023/01/30 4限</td>
            <td>教師１</td>
            <td>承認待ち</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_student-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2023/01/09 19:30</td>
            <td>生徒</td>
            <td>2023/01/29 3限</td>
            <td></td>
            <td>承認待ち</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                {{-- 申請者種別が生徒のため更新ボタン非活性 --}}
                <x-button.list-edit href="{{ route('transfer_student-edit', 2) }}" disabled=true/>
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.transfer_student-modal')

@stop