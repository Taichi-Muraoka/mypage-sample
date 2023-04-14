@extends('adminlte::page')

@section('title', '特別期間講習自動コマ組み')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="mb-3 fix">
        <tr>
            <th width="35%">特別期間</th>
            <td>2023年春期</td>
        </tr>
        <tr>
            <th>確定日</th>
            <td></td>
        </tr>
        <tr>
            <th>状態</th>
            <td>コマ組み完了</td>
        </tr>
    </x-bs.table>

    <x-bs.callout type="warning">
        ・「コマ組み実行」ボタンを押下すると、自動コマ組み処理がバッググラウンドで実行されます。<br>
        　処理が正常に完了したかどうかは、下記の「更新」ボタンを押下しご確認ください。<br>
        ・自動コマ組み処理が正常完了すると、コマ組みできた授業が仮スケジュールとして教室カレンダーに表示されます。<br>
        　また、コマ組みできなかったものはアンマッチリストに出力されます。<br>
        ・自動コマ組み完了の段階では、スケジュール仮確定の状態です。<br>
        　スケジュールを確定し、生徒・講師へ公開するには、「スケジュール確定」ボタンを押下してください。
    </x-bs.callout>

    {{-- 処理実行ボタン --}}
    <div class="d-flex justify-content-end">
        <x-button.submit-exec caption="コマ組み実行" dataTarget="#modal-exec-auto" />
        <x-button.submit-exec caption="スケジュール確定" dataTarget="#modal-exec-confirm" />
    </div>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 結果リスト --}}
    <x-bs.card-list>

        {{-- カードヘッダ右 --}}
        <x-slot name="tools">
            <x-button.submit-href caption="更新" icon="fas fa-sync" :small=true btn="default" onClickPrevent="search" />
        </x-slot>

        {{-- テーブル --}}
        <x-bs.table :button="true">

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th width="25%">処理実行日時</th>
                <th width="20%">実行ステータス</th>
                <th>実行者</th>
                <th width="20%"></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr>
                <td>2023/03/10 16:00:00</td>
                <td>正常終了</td>
                <td>久我山　教室長</td>
                <td>
                <x-button.list-send href="{{ Route('season_plan-download', 1) }}" caption="アンマッチリスト出力" icon="fas fa-download" />
                </td>
            </tr>

        </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル(申込者一覧一括受付・出力確認) 出力 --}}
@include('pages.admin.modal.season_plan_auto-modal', ['modal_send_confirm' => true, 'modal_id' => 'modal-exec-auto'])
{{-- モーダル(スケジュール登録確認) 登録 --}}
@include('pages.admin.modal.season_plan_confirm-modal', ['modal_send_confirm' => true, 'modal_id' => 'modal-exec-confirm'])

@stop