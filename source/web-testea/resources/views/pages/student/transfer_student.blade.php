@extends('adminlte::page')

@section('title', '振替授業調整一覧')

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
            <th>申請日</th>
            <th>申請者種別</th>
            <th>授業日・時限</th>
            <th>コース</th>
            <th>講師名</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10</td>
            <td>講師</td>
            <td>2023/01/30 5限</td>
            <td>個別指導コース</td>
            <td>CWテスト教師１０１</td>
            <td>承認待ち</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_student-edit', 1) }}" caption="承認" />
            </td>
        </tr>
        <tr>
            <td>2023/01/09</td>
            <td>生徒</td>
            <td>2023/01/29 5限</td>
            <td>家庭教師</td>
            <td>CWテスト教師１０１</td>
            <td>承認待ち</td>
            <td>
                <x-button.list-dtl />
                {{-- 申請者種別が生徒のため更新ボタン非活性 --}}
                <x-button.list-edit href="{{ route('transfer_student-edit', 2) }}" caption="承認" disabled=true/>
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.transfer_student-modal')

@stop