@extends('adminlte::page')

@section('title', '問い合わせ一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="宛先校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="宛先校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="contact_state" caption="ステータス" :select2=true :mastrData=$contactState :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$students :editData=$editData
                :rules=$rules :select2Search=true :blank=true />
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
            <td>@{{$filters.formatYmd(item.regist_time)}}</td>
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