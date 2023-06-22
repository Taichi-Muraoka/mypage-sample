@extends('adminlte::page')

@section('title', 'お知らせ一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">通知日</th>
            <th>タイトル</th>
            <th>送信元教室</th>
            <th width="20%">送信者</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="通知日" class="t-minimum">@{{item.date|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="タイトル">@{{item.title}}</x-bs.td-sp>
            <x-bs.td-sp caption="送信元教室">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="送信者">@{{item.sender}}</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => 'item.id']" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
{{-- 模試・イベント申込 --}}
{{-- @include('pages.mypage-common.modal.notice_event-modal', ['modal_id' => 'modal-dtl-event']) --}}
{{-- 短期個別講習案内 --}}
{{-- @include('pages.mypage-common.modal.notice_course-modal', ['modal_id' => 'modal-dtl-course']) --}}
{{-- それ以外 --}}
{{-- @include('pages.mypage-common.modal.notice_absent-modal', ['modal_id' => 'modal-dtl-absent']) --}}
{{-- 共通のモーダルとした --}}
@include('pages.mypage-common.modal.notice-modal')

@stop