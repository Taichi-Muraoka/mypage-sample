@extends('adminlte::page')

@section('title', '生徒成績一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="在籍教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="在籍教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="cls_cd" caption="学年" :select2=true :editData=$editData :mastrData=$classes />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">登録日</th>
            <th width="20%">生徒名</th>
            <th width="15%">試験種別</th>
            <th>試験名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="登録日">@{{item.regist_time|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.sname}}</x-bs.td-sp>
            <x-bs.td-sp caption="試験種別">@{{item.type_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="試験名">@{{item.teiki_name}}@{{item.moshi_name}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                <x-button.list-edit vueHref="'{{ route('grades_mng-edit', '') }}/' + item.id" />
                </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.grades_mng-modal')

@stop