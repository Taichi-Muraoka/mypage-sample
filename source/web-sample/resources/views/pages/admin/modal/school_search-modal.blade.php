@extends('pages.common.modal')

@section('modal-title','学校検索')

@section('modal-body')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="school_kind_cd" caption="学校種" :select2=true :mastrData=$schoolKindList
            :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="establish_kind" caption="設置区分" :select2=true :mastrData=$establishKindList
            :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="school_cd" caption="学校コード" :rules=$rulesSchool/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="school_name" caption="学校名" :rules=$rulesSchool/>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>学校コード</th>
            <th>学校種</th>
            <th>設置区分</th>
            <th>学校名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.school_cd}}</td>
            <td>@{{item.school_kind_name}}</td>
            <td>@{{item.establish_name}}</td>
            <td>@{{item.school_name}}</td>
            <td>
                <x-button.list-select
                    :vueDataAttr="['school_cd' => 'item.school_cd', 'school_name' => 'item.school_name']" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@overwrite