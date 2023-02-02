@extends('adminlte::page')

@section('title', '模試情報一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="模試名" id="name" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="学年" id="cls_cd" :select2=true :mastrData=$cls />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="開催日 From" id="trial_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="開催日 To" id="trial_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('trial_mng-new') }}" caption="模試情報取込" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>模試名</th>
            <th width="15%">学年</th>
            <th width="15%">開催日</th>
            <th width="15%">受付未対応数</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.name}}</td>
            <td>@{{item.cls}}</td>
            <td>@{{item.trial_date|formatYmd}}</td>
            <td>@{{item.count}}</td>

            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.tmid']" />
                <x-button.list-edit vueHref="'{{ route('trial_mng-entry', '') }}/' + item.tmid" caption="申込者" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.trial_mng-modal')

@stop