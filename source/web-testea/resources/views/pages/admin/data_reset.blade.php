@extends('adminlte::page')

@section('title', '保持期限超過データ削除管理')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>保持期限超過データ削除処理のログ情報を確認できます。</p>

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
            <th>処理日時</th>
            <th>終了ステータス</th>
            <th></th>
        </x-slot>
        {{-- テーブル行 --}}
        <tr>
            <td>2023/02/28 16:00</td>
            <td>正常終了</td>
            <td>
                <x-button.submit-exec caption="バックアップデータ出力" icon="fas fa-download" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop