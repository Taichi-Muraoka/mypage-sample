@extends('adminlte::page')

@section('title', '学年情報更新')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>新年度の学年更新処理を実行します。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">現在の年度</th>
            <td>2023</td>
        </tr>
        <tr>
            <th>新年度</th>
            <td>2024</td>
        </tr>
    </x-bs.table>

    <x-bs.callout type="warning">
        送信ボタン押下後、バッググラウンドで処理されます。<br>
        (他の処理が実行中の場合は送信できません)<br>
        処理が正常に完了したかどうかは、下記の実行履歴よりご確認ください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new caption='学年更新実行'/>
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
            <th width="25%">処理開始日時</th>
            <th width="25%">処理終了日時</th>
            <th width="20%">終了ステータス</th>
            <th width="15%">処理件数</th>
            <th width="15%">実行者</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/10 16:00</td>
            <td>2023/03/10 16:05</td>
            <td>正常終了</td>
            <td>100</td>
            <td>久我山　教室長</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>


@stop