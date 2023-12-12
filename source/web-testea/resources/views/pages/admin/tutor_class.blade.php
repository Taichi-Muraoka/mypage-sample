@extends('adminlte::page')

@section('title', '講師授業集計')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=false/>
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false/>
            @endcan
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="target_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="target_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">講師ID</th>
            <th width="20%">講師名</th>
            <th width="5%">個別</th>
            <th width="6%">１対２</th>
            <th width="6%">１対３</th>
            <th width="5%">集団</th>
            <th width="5%">家庭教師</th>
            <th width="5%">演習</th>
            <th width="5%">ハイプラン</th>
            <th width="5%">代講(受)</th>
            <th width="5%">緊急代講(受)</th>
            <th width="5%">代講(出)</th>
            <th width="5%">緊急代講(出)</th>
            <th width="5%">初回体験授業</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.tutor_id}}</td>
            <td>@{{item.tutor_name}}</td>
            <td class="t-price">@{{item.personal_min}}</td>
            <td class="t-price">@{{item.two_min}}</td>
            <td class="t-price">@{{item.three_min}}</td>
            <td class="t-price">@{{item.group_min}}</td>
            <td class="t-price">@{{item.home_min}}</td>
            <td class="t-price">@{{item.exercise_min}}</td>
            <td class="t-price">@{{item.high_min}}</td>
            <td class="t-price">@{{item.normal_sub_get}}</td>
            <td class="t-price">@{{item.emergency_sub_get}}</td>
            <td class="t-price">@{{item.normal_sub_out}}</td>
            <td class="t-price">@{{item.emergency_sub_out}}</td>
            <td class="t-price">@{{item.trial_class}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['tutor_id' => 'item.tutor_id',
                    'campus_cd' => 'item.campus_cd',
                    'target_date_from' => 'item.target_date_from',
                    'target_date_to' => 'item.target_date_to']" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.tutor_class-modal')

@stop