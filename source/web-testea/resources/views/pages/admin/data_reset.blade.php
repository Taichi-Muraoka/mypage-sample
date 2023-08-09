@extends('adminlte::page')

@section('title', '保持期限超過データ削除管理')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>保持期限超過データ削除処理の実行ログを確認します。<br>
        バックアップ出力ボタン押下で、削除されたデータをCSV形式でダウンロードできます。</p>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-href caption="更新" icon="fas fa-sync" :small=true btn="default" onClickPrevent="search" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">処理開始日時</th>
            <th width="20%">処理終了日時</th>
            <th>終了ステータス</th>
            <th width="15%"></th>
        </x-slot>
        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/01 00:00</td>
            <td>2023/03/01 00:05</td>
            <td>正常終了</td>
            <td>
                <x-button.submit-exec caption="バックアップ出力" icon="fas fa-download" />
            </td>
        </tr>
        <tr>
            <td>2022/03/01 00:00</td>
            <td>2022/03/01 00:04</td>
            <td>正常終了</td>
            <td>
                <x-button.submit-exec caption="バックアップ出力" icon="fas fa-download" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop