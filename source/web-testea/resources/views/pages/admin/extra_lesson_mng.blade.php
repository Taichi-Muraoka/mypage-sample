@extends('adminlte::page')

@section('title', '追加授業依頼一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :select2Search=false
                :blank=false />
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :select2Search=false />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="status" caption="ステータス" :select2=true :mastrData=$statusList :select2Search=false
                :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$studentList :select2Search=true
                :blank=true />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>
    {{-- テーブル --}}
    <x-bs.table :button=true>
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">依頼日</th>
            <th>校舎</th>
            <th>生徒名</th>
            <th class="t-minimum">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.status_name}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['extra_apply_id' => 'item.extra_apply_id']" />
                <x-button.list-edit vueHref="'{{ route('extra_lesson_mng-new', ['', '']) }}/' + item.student_id + '/' + item.campus_cd" caption="授業追加" />
                <x-button.list-edit vueHref="'{{ route('extra_lesson_mng-edit', '') }}/' + item.extra_apply_id" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.extra_lesson_mng-modal')

@stop