@extends('adminlte::page')

@section('title', '模試申込者一覧')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="mb-3 fix">
        <tr>
            <th width="35%">模試名</th>
            <td>{{$trial->name}}</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$trial->cls}}</td>
        </tr>
        <tr>
            <th>開催日</th>
            <td>{{$trial->trial_date->format('Y/m/d')}}</td>
        </tr>
    </x-bs.table>

    {{-- 結果リスト --}}
    <x-bs.card-list>

        {{-- 検索時にIDを送信 --}}
        <x-input.hidden id="tmid" :editData=$trial />

        {{-- テーブル --}}
        <x-bs.table :button=true>

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th width="15%">申込日</th>
                <th>生徒名</th>
                <th width="15%">ステータス</th>
                <th></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                <td>@{{item.apply_time|formatYmd}}</td>
                <td>@{{item.name}}</td>
                <td>@{{item.apply_state}}</td>

                <td>
                    {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                    <x-button.list-dtl :vueDataAttr="['trial_apply_id' => 'item.trial_apply_id']" />
                    {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                    <x-button.list-edit
                        vueHref="'{{ route('trial_mng-entry', ['','']) }}/' + item.tmid + '/edit/' + item.trial_apply_id" />
                </td>
            </tr>

        </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <div class="d-flex justify-content-end">
                <x-button.submit-exec caption="一覧出力" dataTarget="#modal-dtl-output" icon="fas fa-download"
                    :dataAttr="['tmid' => $trial->tmid]" />
            </div>
        </div>
    </x-slot>
</x-bs.card>

{{-- 詳細 --}}
@include('pages.admin.modal.trial_mng_entry-modal')
{{-- モーダル(一括受付・ファイル出力確認) 出力 --}}
@include('pages.admin.modal.trial_mng_output-modal', ['modal_send_confirm' => true, 'modal_id' => 'modal-dtl-output'])

@stop