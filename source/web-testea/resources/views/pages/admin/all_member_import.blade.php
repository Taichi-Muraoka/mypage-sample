@extends('adminlte::page')

@section('title', '学年更新管理')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>システム内の年度切り替え および 生徒学年情報の更新処理の実行ログを確認します。</p>

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
            <th width="20%">終了ステータス</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/01 00:00</td>
            <td>2023/03/01 00:05</td>
            <td>正常終了</td>
        </tr>
        <tr>
            <td>2022/03/01 00:00</td>
            <td>2022/03/01 00:04</td>
            <td>正常終了</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>


@stop