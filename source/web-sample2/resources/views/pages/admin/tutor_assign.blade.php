@extends('adminlte::page')

@section('title', '空き講師検索')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=false :rules=$rules />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=true :rules=$rules />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師名" :select2=true :mastrData=$tutors :editData=$editData
                :rules=$rules :select2Search=true :blank=true :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="gender_cd" caption="性別" :select2=true :mastrData=$genderList :editData=$editData
                :rules=$rules :select2Search=false :blank=true  />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="school_u" caption="在籍大学" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="school_h" caption="出身高校" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="school_j" caption="出身中学" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="day_cd" caption="曜日" :select2=true :mastrData=$dayList :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="period_no" caption="時限" :select2=true :mastrData=$periods :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="subject_cd" caption="担当科目" :select2=true :mastrData=$subjects :editData=$editData
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
            <th>校舎</th>
            <th>講師名</th>
            <th>曜日</th>
            <th>時限</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>@{{item.day_name}}曜</td>
            <td>@{{item.period_no}}限</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['free_period_id' => 'item.free_period_id']" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.tutor_assign-modal')

@stop
