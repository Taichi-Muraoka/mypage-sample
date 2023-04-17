@extends('adminlte::page')

@section('title', '生徒成績一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- 校舎リスト選択時、onChangeによる生徒リストの絞り込みを行う。-1の場合は自分の受け持ちの生徒だけに絞り込み --}}
            {{-- <x-input.select caption="校舎" id="roomcd" :select2=true onChange="selectChangeGetRoom" :editData=$editData
                :mastrData=$rooms :select2Search=false emptyValue="-1" /> --}}
            <x-input.select id="roomcd" caption="校舎" :select2=false >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">本郷</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            {{-- <x-input.select caption="生徒名" id="sid" :select2=true :editData=$editData> --}}
                {{-- vueで動的にプルダウンを作成 --}}
                {{-- <option v-for="item in selectGetItem.selectItems" :value="item.id">
                    @{{ item.value }}
                </option>
            </x-input.select> --}}
            <x-input.select caption="生徒名" id="student" :select2=true :editData="$editData">
                <option value="1">CWテスト生徒１</option>
                <option value="2">CWテスト生徒２</option>
                <option value="3">CWテスト生徒３</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">登録日</th>
            <th width="15%">生徒名</th>
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
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.grades_check-modal')

@stop