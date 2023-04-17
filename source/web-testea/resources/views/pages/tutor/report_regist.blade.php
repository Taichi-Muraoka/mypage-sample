@extends('adminlte::page')

@section('title', '授業報告書一覧')

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
    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="コース" id="course" :select2=true :editData=$editData>
                <option value="1">個別指導</option>
                <option value="2">集団授業</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="承認ステータス" id="status" :select2=true :editData=$editData>
                <option value="1">承認待ち</option>
                <option value="2">承認</option>
                <option value="3">差戻し</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('report_regist-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">授業日時</th>
            <th>時限</th>
            <th width="20%">校舎</th>
            <th>コース</th>
            <th>生徒名</th>
            <th width="15%">承認ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="授業日時">@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</x-bs.td-sp>
            <x-bs.td-sp caption="時限"></x-bs.td-sp>
            <x-bs.td-sp caption="校舎">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="コース">個別指導</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.sname}}</x-bs.td-sp>
            <x-bs.td-sp caption="承認ステータス"></x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                <x-button.list-edit vueHref="'{{ route('report_regist-edit', '') }}/' + item.id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.report_regist-modal')

@stop