@extends('adminlte::page')

@section('title', '時間割マスタ管理')

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
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select caption="時間割区分" id="timetable_kind" :select2=true :mastrData=$timetablekind :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_timetable-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">校舎</th>
            <th>時間割区分</th>
            <th width="15%">時限</th>
            <th width="15%">開始時刻</th>
            <th width="15%">終了時刻</th>
            <th width="10%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- MEMO: 日付フォーマットを指定する --}}
            <td>@{{item.campus_name}}</td>
            <td>@{{item.kind_name}}</td>
            <td>@{{item.period_no}}</td>
            <td>@{{item.start_time|formatHm}}</td>
            <td>@{{item.end_time|formatHm}}</td>
            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('master_mng_timetable-edit', '') }}/' + item.id" />
            </td>
        </tr>
        
    </x-bs.table>
</x-bs.card-list>

@stop