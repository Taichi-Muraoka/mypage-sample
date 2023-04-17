@extends('adminlte::page')

@section('title', '振替残数リセット処理')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>振替が実施されていない授業を無効にし、振替授業の残数をリセットします。<br>
        対象の授業は[授業管理]>[要振替授業一覧]で確認してください。</p>

    <x-input.text caption="年度末年月" id="this_year" :rules=$rules :editData=$editData/>

    <x-bs.callout type="warning">
        送信ボタン押下後、バッググラウンドで処理されます。<br>
        (他の処理が実行中の場合は送信できません)<br>
        処理が正常に完了したかどうかは、下記の実行履歴よりご確認ください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new caption='更新実行'/>
        </div>
    </x-slot>

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
            <th width="25%">年度末年月</th>
            <th width="25%">処理日</th>
            <th width="15%">処理件数</th>
            <th width="15%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023年2月</td>
            <td>2023/01/30</td>
            <td>100</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.transfer_reset-modal')

@stop