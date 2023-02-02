@extends('adminlte::page')

@section('title', 'イベント申込者一覧')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="mb-3 fix">
        <tr>
            <th width="35%">イベント名</th>
            <td>{{$event->name}}</td>
        </tr>
        <tr>
            <th>対象</th>
            <td>{{$event->cls}}</td>
        </tr>
        <tr>
            <th>開催日</th>
            <td>{{$event->event_date->format('Y/m/d')}}</td>
        </tr>
    </x-bs.table>

    {{-- 結果リスト --}}
    <x-bs.card-list>

        {{-- 検索時にIDを送信 --}}
        <x-input.hidden id="event_id" :editData=$event />

        {{-- テーブル --}}
        <x-bs.table :button="true">

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th width="15%">申込日</th>
                <th>生徒名</th>
                <th width="15%">参加人数</th>
                <th width="15%">ステータス</th>
                <th></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                <td>@{{item.apply_time|formatYmd}}</td>
                <td>@{{item.name}}</td>
                <td>@{{item.members}}</td>
                <td>@{{item.changes_state}}</td>

                <td>
                    {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                    <x-button.list-dtl :vueDataAttr="['event_apply_id' => 'item.event_apply_id']" />
                    {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                    <x-button.list-edit
                        vueHref="'{{ route('event_mng-entry', ['','']) }}/' + item.event_id + '/edit/' + item.event_apply_id" />
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
                    :dataAttr="['event_id' => $event->event_id]" />
                <x-button.submit-exec caption="スケジュール登録" dataTarget="#modal-dtl-new" icon="fas fa-calendar-plus"
                    :dataAttr="['event_id' => $event->event_id]" />
            </div>
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.event_mng_entry-modal')
{{-- モーダル(申込者一覧一括受付・出力確認) 出力 --}}
@include('pages.admin.modal.event_mng_output-modal', ['modal_send_confirm' => true, 'modal_id' => 'modal-dtl-output'])
{{-- モーダル(スケジュール登録確認) 登録 --}}
@include('pages.admin.modal.event_mng_new-modal', ['modal_send_confirm' => true, 'modal_id' => 'modal-dtl-new'])
@stop