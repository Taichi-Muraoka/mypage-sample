@extends('adminlte::page')

@section('title', '振替授業調整一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="roomcd" caption="在籍教室" :select2=true :mastrData=$rooms :editData=$editData />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_tutor-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>登録日時</th>
            <th>申請者種別</th>
            <th>授業日・時限</th>
            <th>生徒名</th>
            <th>承認ステータス</th>
            <th>事務局ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10 17:00</td>
            <td>教師</td>
            <td>2023/01/30 4限</td>
            <td>CWテスト生徒１</td>
            <td>承認待ち</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                {{-- 申請者種別が教師のため更新ボタン非活性 --}}
                <x-button.list-edit href="{{ route('transfer_tutor-edit', 1) }}" disabled="true"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/09 19:30</td>
            <td>生徒</td>
            <td>2023/01/29 3限</td>
            <td>CWテスト生徒１</td>
            <td>承認待ち</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_tutor-edit', 2) }}"/>
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.transfer_tutor-modal')

@stop