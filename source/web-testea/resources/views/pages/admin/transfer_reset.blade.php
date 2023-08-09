@extends('adminlte::page')

@section('title', '振替残数リセット')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>対象年度（3月～翌年2月）の授業を対象に処理を実行します。<br>
        振替依頼がされていない未振替の授業に対し、リセット（実施扱い）とします。<br>
        リスト出力ボタン押下で、リセットされた授業情報をCSV形式でダウンロードできます。</p>

    <x-input.text caption="対象年度" id="this_year" :rules=$rules :editData=$editData/>

    {{-- <x-bs.callout type="warning"> --}}
    {{--     送信ボタン押下後、バッググラウンドで処理されます。<br> --}}
    {{--     (他の処理が実行中の場合は送信できません)<br> --}}
    {{--     処理が正常に完了したかどうかは、下記の実行履歴よりご確認ください。 --}}
    {{-- </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new caption='処理実行'/>
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
            <th width="20%">処理実行日時</th>
            <th width="20%">対象年度</th>
            <th width="20%">終了ステータス</th>
            <th>実行者</th>
            <th width="15%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/01 15:00</td>
            <td>2022年度</td>
            <td>正常終了</td>
            <td>本部管理者１</td>
            <td>
                <x-button.submit-exec caption="リスト出力" icon="fas fa-download" />
            </td>
        </tr>
        <tr>
            <td>2022/03/01 10:00</td>
            <td>2021年度</td>
            <td>正常終了</td>
            <td>本部管理者１</td>
            <td>
                <x-button.submit-exec caption="リスト出力" icon="fas fa-download" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop