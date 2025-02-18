@extends('adminlte::page')

@section('title', '研修一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="形式" id="trn_type" :select2=true :mastrData=$training_type
                :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="登録日" id="regist_time" />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="内容" id="text" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('training_mng-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">形式</th>
            <th>内容</th>
            <th width="10%">登録日</th>
            <th width="10%">期限</th>
            <th width="10%">公開日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.trn_type}}</td>
            <td>@{{item.text}}</td>
            <td>@{{$filters.formatYmd(item.regist_time)}}</td>
            <td><span v-if="item.limit_date">@{{$filters.formatYmd(item.limit_date)}}</span><span v-else>無期限</span></td>
            <td>@{{$filters.formatYmd(item.release_date)}}</td>

            <td>
                <x-button.list-dtl vueHref="'{{ route('training_mng-state', '') }}/' + item.trn_id" caption="閲覧状況" />
                <x-button.list-edit vueHref="'{{ route('training_mng-edit', '') }}/' + item.trn_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop