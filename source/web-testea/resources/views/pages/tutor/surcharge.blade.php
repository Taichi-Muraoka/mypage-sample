@extends('adminlte::page')

@section('title', '追加請求申請一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('surcharge-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日時</th>
            <th>請求種別</th>
            <th>時間数</th>
            <th>費用</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10 17:00</td>
            <td>事務作業</td>
            <td>60</td>
            <td>1000</td>
            <td>承認</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.surcharge-modal')

@stop