@extends('pages.common.modal')

@section('modal-title','学校検索')

@section('modal-body')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="school_id" caption="学校コード" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="kinds" caption="学校種" :select2=true>
                <option value="1">小学校</option>
                <option value="2">中学校</option>
                <option value="3">高校</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="pref" caption="都道府県" :select2=true>
                <option value="1">千葉</option>
                <option value="2">東京</option>
                <option value="3">神奈川</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="division" caption="設置区分" :select2=true>
                <option value="1">国立</option>
                <option value="2">公立</option>
                <option value="3">私立</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="address" caption="住所" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="school_name" caption="学校名" :rules=$rules />
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
            <th>都道府県</th>
            <th>設置区分</th>
            <th>学校名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.school_code}}</td>
            <td>@{{item.school_type}}</td>
            <td>@{{item.school_pref}}</td>
            <td>@{{item.school_div}}</td>
            <td>@{{item.school_name}}</td>
            <td>
                <x-button.list-select
                    :vueDataAttr="['school_id' => 'item.school_id', 'school_name' => 'item.school_name']" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@overwrite