@extends('adminlte::page')

@section('title', '生徒カルテ一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="在籍教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="在籍教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="kinds" caption="カルテ種別" :select2=true :editData=$editData>
                <option value="1">面談記録</option>
                <option value="2">電話記録</option>
                <option value="3">その他</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list :mock=true>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('karte-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">登録日時</th>
            <th>生徒名</th>
            <th>カルテ種別</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10 17:00</td>
            <td>CWテスト生徒１</td>
            <td>面談記録</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('karte-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2023/01/09 19:30</td>
            <td>CWテスト生徒２</td>
            <td>電話記録</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('karte-edit', 2) }}" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.karte-modal')

@stop