@extends('adminlte::page')

@section('title', '授業教材マスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$grades :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="l_subject_cd" caption="授業教科" :select2=true :mastrData=$subjects :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="t_subject_cd" caption="教材教科" :select2=true :mastrData=$textSubjects :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_text-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>教材コード</th>
            <th>学年</th>
            <th>授業教科</th>
            <th>教材教科</th>
            <th>名称</th>
            <th width="7%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- MEMO: 日付フォーマットを指定する --}}
            <td>@{{item.text_cd}}</td>
            <td>@{{item.grade_name}}</td>
            <td>@{{item.l_subject_name}}</td>
            <td>@{{item.t_subject_name}}</td>
            <td>@{{item.name}}</td>
            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('master_mng_text-edit', '') }}/' + item.text_cd" />
            </td>
        </tr>

    </x-bs.table>
</x-bs.card-list>

@stop