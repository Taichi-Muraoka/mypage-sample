@extends('adminlte::page')

@section('title', '問い合わせ一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="宛先" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="宛先" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="contact_state" caption="ステータス" :select2=true :mastrData=$contactState />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">問い合わせ日</th>
            <th width="15%">宛先</th>
            <th width="15%">生徒名</th>
            <th>件名</th>
            <th width="15%">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.regist_time|formatYmd}}</td>
            <td>@{{item.room_name}}</td>
            <td>@{{item.sname}}</td>
            <td>@{{item.title}}</td>
            <td>@{{item.contact_state}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.contact_id']" />
                <x-button.list-edit vueHref="'{{ route('contact_mng-edit', ['','']) }}/' + item.contact_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.contact_mng-modal')

@stop