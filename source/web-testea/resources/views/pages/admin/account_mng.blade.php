@extends('adminlte::page')

@section('title', '管理者アカウント管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=true />
            @endcan
        </x-bs.col2>

        <x-bs.col2>
            <x-input.text caption="管理者名" id="name" :rules=$rules />
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
            <th width="15%">管理者ID</th>
            <th>管理者名</th>
            <th width="30%">校舎</th>
            <th>メールアドレス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.adm_id}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.email}}</td>
            <td>@{{item.room_name}}</td>

            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit
                    vueHref="'{{ route('account_mng-edit', '') }}/' + item.adm_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}

@stop