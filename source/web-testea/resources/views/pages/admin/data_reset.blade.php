@extends('adminlte::page')

@section('title', '保持期限データ削除')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>保持期限を超えたデータをシステムから削除します。<br>
        削除する基準となる年月を指定してください。</p>

    <x-input.text caption="基準年月" id="this_year" :rules=$rules :editData=$editData/>

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