@extends('adminlte::page')

@section('title', '休業日一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="休業日 From" id="holiday_date_from" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="休業日 To" id="holiday_date_to" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('room_holiday-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="45%">休業日</th>
            <th>教室</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- MEMO: 日付フォーマットを指定する --}}
            <td>@{{item.holiday_date|formatYmd}}</td>
            {{-- MEMO: JOINで紐付いたname(ここではusersのname項目)を表示 --}}
            <td>@{{item.room_name}}</td>
            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit
                    vueHref="'{{ route('room_holiday-edit', ['','']) }}/' + item.room_holiday_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop