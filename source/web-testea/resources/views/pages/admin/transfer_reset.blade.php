@extends('adminlte::page')

@section('title', '振替残数リセット処理')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>振替が実施されていない授業を無効にし、振替授業の残数をリセットします。<br>
        対象年度（3月～翌年2月）の授業を対象に処理を実行します。<br>
        対象の授業は[授業管理]>[要振替授業一覧]で確認してください。</p>

    <x-input.text caption="対象年度" id="this_year" :rules=$rules :editData=$editData/>

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
            <th>処理日時</th>
            <th>対象年度</th>
            <th>終了ステータス</th>
            <th>処理件数</th>
            <th>実行者</th>
            <th width="15%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/02/28 16:00</td>
            <td>2022年度</td>
            <td>正常終了</td>
            <td>100</td>
            <td>久我山　教室長</td>
            <td>
                <x-button.submit-exec caption="更新データリスト出力" icon="fas fa-download" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop