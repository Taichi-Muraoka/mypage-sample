@extends('adminlte::page')

@section('title', '給与算出情報一覧')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="mb-3 fix">
        <tr>
            <th width="35%">対象年月</th>
            <td>2023年03月</td>
        </tr>
        <tr>
            <th>確定日</th>
            <td>2023/03/31</td>
        </tr>
        <tr>
            <th>合計金額</th>
            <td>1,000,000</td>
        </tr>
        <tr>
            <th>状態</th>
            <td>集計済</td>
        </tr>
    </x-bs.table>

    <div class="d-flex justify-content-end">
        <x-button.submit-exec caption="集計実行" dataTarget="#modal-dtl-output" />
        <x-button.submit-exec caption="確定処理" dataTarget="#modal-dtl-new" />
    </div>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 結果リスト --}}
    <x-bs.card-list>

        {{-- カードヘッダ右 --}}
        <x-slot name="tools">
            <x-button.submit-href caption="更新" icon="fas fa-sync" :small=true btn="default" onClickPrevent="search" />
        </x-slot>

        {{-- 検索時にIDを送信 --}}
        <x-input.hidden id="event_id" />

        {{-- テーブル --}}
        <x-bs.table :button="true">

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th width="15%">講師ID</th>
                <th>講師名</th>
                <th>金額</th>
                <th></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr>
                <td>101</td>
                <td>CWテスト講師１０１</td>
                <td>77,000</td>
                <td>
                    {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                    <x-button.list-dtl/>
                </td>
            </tr>

        </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            <div class="d-flex justify-content-end">
                <x-button.submit-exec caption="データ出力" dataTarget="#modal-dtl-output" icon="fas fa-download" />
            </div>
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.salary_calculation-detail-modal')

@stop