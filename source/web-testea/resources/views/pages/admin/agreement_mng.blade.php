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

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('agreement_mng-new', $sid) }}" :small=true />
    </x-slot>

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
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="授業種別">個別</x-bs.td-sp>
            <x-bs.td-sp caption="契約コース名">個別指導 中学生コース（受験準備学年） 月4回 90分</x-bs.td-sp>
            <x-bs.td-sp caption="開始日">2023/03/01</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">2024/02/29</x-bs.td-sp>
            <x-bs.td-sp caption="金額" class="t-price">33,880</x-bs.td-sp>
            <x-bs.td-sp caption="単価" class="t-price">8,470</x-bs.td-sp>
            <x-bs.td-sp caption="回数" class="t-price">4</x-bs.td-sp>
        </tr>
        <tr>
            <x-bs.td-sp caption="授業種別">集団</x-bs.td-sp>
            <x-bs.td-sp caption="契約コース名">集団授業 中学生 英語・数学総復習パック</x-bs.td-sp>
            <x-bs.td-sp caption="開始日">2022/07/01</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">2022/08/31</x-bs.td-sp>
            <x-bs.td-sp caption="金額" class="t-price">50,000</x-bs.td-sp>
            <x-bs.td-sp caption="単価" class="t-price">5,000</x-bs.td-sp>
            <x-bs.td-sp caption="回数" class="t-price">10</x-bs.td-sp>
        </tr>

    </x-bs.table>
</x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('member_mng-detail', $sid)}}" />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.agreement_mng-modal')

@stop