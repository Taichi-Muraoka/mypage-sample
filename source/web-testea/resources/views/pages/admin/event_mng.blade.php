@extends('adminlte::page')

@section('title', 'イベント一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="イベント名" id="name" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            {{-- <x-input.select caption="学年" id="cls_cd" :select2=true :mastrData=$cls /> --}}
            <x-input.select id="cls_cd" caption="学年" :select2=true >
                <option value="1">高3</option>
                <option value="2">高2</option>
                <option value="3">高1</option>
                <option value="4">中3</option>
                <option value="5">中2</option>
                <option value="6">中1</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="開催日 From" id="event_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="開催日 To" id="event_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('event_mng-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>イベント名</th>
            <th width="15%">学年</th>
            <th width="15%">開催日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.name}}</td>
            <td>@{{item.cls}}</td>
            <td>@{{item.event_date|formatYmd}}</td>

            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.event_id']" />
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('event_mng-edit', '') }}/' + item.event_id" />
                <x-button.list-edit vueHref="'{{ route('event_mng-entry', '') }}/' + item.event_id" caption="申込者" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.event_mng-modal')

@stop