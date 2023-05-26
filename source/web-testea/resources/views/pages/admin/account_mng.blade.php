@extends('adminlte::page')

@section('title', '事務局アカウント管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 校舎管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="管理校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="管理校舎" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>

        <x-bs.col2>
            <x-input.text caption="氏名" id="name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('account_mng-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">事務局ID</th>
            <th>氏名</th>
            <th width="30%">管理校舎</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.adm_id}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.room_name}}</td>

            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.adm_id']" />
                    {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit
                    vueHref="'{{ route('account_mng-edit', '') }}/' + item.adm_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.account_mng-modal')

@stop