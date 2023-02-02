@extends('adminlte::page')

@section('title', '年度スケジュール取込')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>新年度の全生徒のスケジュール情報を取込みます。</p>

    <x-input.file caption="スケジュール情報ファイル" id="upload_file" />

    <x-bs.callout>
        ファイル形式：ZIP形式
    </x-bs.callout>

    <x-bs.callout type="warning">
        送信ボタン押下後、バッググラウンドで処理されます。<br>
        (他の処理が実行中の場合は送信できません)<br>
        処理が正常に完了したかどうかは、下記の実行履歴よりご確認ください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
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
            <th width="15%">教室</th>
            <th width="15%">実行者</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.start_time|formatYmdHms}}</td>
            <td>@{{item.end_time|formatYmdHms}}</td>
            <td><span v-if="item.batch_state == {{ App\Consts\AppConst::CODE_MASTER_22_1 }}" class="text-danger">@{{item.batch_state_name}}</span><span v-else>@{{item.batch_state_name}}</span></td>
            <td>@{{item.room_name}}</td>
            <td>@{{item.executor}}</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop