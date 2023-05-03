@extends('adminlte::page')

@section('title', '特別期間講習 確定状況一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="30%">特別期間</th>
            <th>状態</th>
            <th>確定日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023年春期</td>
            <td>未確定</td>
            <td></td>
            <td>
                <x-button.list-dtl caption="スケジュール確定" btn="btn-primary" dataTarget="#modal-exec-confirm" />
            </td>
        </tr>
        <tr>
            <td>2022年冬期</td>
            <td>確定済</td>
            <td>2022/12/10</td>
            <td>
                <x-button.list-dtl caption="スケジュール確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>
{{-- モーダル(スケジュール確定) --}}
@include('pages.admin.modal.season_plan_confirm-modal', ['modal_send_confirm' => true, 'modal_id' =>'modal-exec-confirm'])

@stop