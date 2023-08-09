@extends('adminlte::page')

@section('title', '生徒学年情報更新')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>システム内の年度切り替え および 生徒学年情報の更新処理を実行します。</p>

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

    {{-- <x-bs.callout type="warning"> --}}
    {{--     送信ボタン押下後、バッググラウンドで処理されます。<br> --}}
    {{--     (他の処理が実行中の場合は送信できません)<br> --}}
    {{--     処理が正常に完了したかどうかは、下記の実行履歴よりご確認ください。 --}}
    {{-- </x-bs.callout> --}}

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
            <th width="20%">終了ステータス</th>
            <th>実行者</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/01 16:00</td>
            <td>正常終了</td>
            <td>本部管理者１</td>
        </tr>
        <tr>
            <td>2022/03/01 10:00</td>
            <td>正常終了</td>
            <td>本部管理者２</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>


@stop