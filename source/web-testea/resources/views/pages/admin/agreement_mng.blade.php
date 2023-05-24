@extends('adminlte::page')

@section('title', '契約情報一覧')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $sid))

@section('parent_page_title', '生徒カルテ')

@section('content')

<x-bs.card>
    <x-slot name="card_title">
        {{$name}}
    </x-slot>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">授業種別</th>
            <th>契約コース名</th>
            <th width="10%">開始日</th>
            <th width="10%">終了日</th>
            <th width="10%">金額</th>
            <th width="10%">単価</th>
            <th width="10%">回数</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>個別</td>
            <td>個別指導 中学生コース（受験準備学年） 月4回 90分</td>
            <td>2023/03/01</td>
            <td>2024/02/29</td>
            <td class="t-price">33,880</td>
            <td class="t-price">8,470</td>
            <td class="t-price">4</td>
            <td>
                <x-button.list-edit href="{{ route('agreement_mng-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>集団</td>
            <td>集団授業 中学生 英語・数学総復習パック</td>
            <td>2022/07/01</td>
            <td>2022/08/31</td>
            <td class="t-price">50,000</td>
            <td class="t-price">5,000</td>
            <td class="t-price">10</td>
            <td>
                <x-button.list-edit href="{{ route('agreement_mng-edit', 1) }}" />
            </td>
        </tr>

    </x-bs.table>
</x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{ route('member_mng-detail', 1) }}" />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.agreement_mng-modal')

@stop