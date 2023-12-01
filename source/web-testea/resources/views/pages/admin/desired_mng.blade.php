@extends('adminlte::page')

@section('title', '受験校一覧')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['student_id']))

@section('parent_page_title', '生徒カルテ')

@section('content')

<x-bs.card>
    <x-slot name="card_title">
        {{$name}}
    </x-slot>

    {{-- 結果リスト --}}
    <x-bs.card-list>
        {{-- テーブル --}}
        <x-bs.table :button=true>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th class="t-minimum">受験年度</th>
                <th class="t-minimum">志望順</th>
                <th>受験校</th>
                <th>学部・学科名</th>
                <th>受験日程名</th>
                <th>受験日</th>
                <th>合否</th>
                <th></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                <td>@{{item.exam_year}}</td>
                <td>@{{item.priority_no}}</td>
                <td>@{{item.school_name}}</td>
                <td>@{{item.department_name}}</td>
                <td>@{{item.exam_name}}</td>
                <td>@{{$filters.formatYmdDay(item.exam_date)}}</td>
                <td>@{{item.result_name}}</td>
                <td>
                    <x-button.list-dtl :vueDataAttr="['id' => 'item.student_exam_id']" />
                    {{-- ボタンスペース --}}
                    &nbsp;
                    <x-button.list-edit vueHref="'{{ route('desired_mng-edit', '') }}/' + item.student_exam_id"
                        vueDisabled="item.disabled_btn" />
                </td>
            </tr>
        </x-bs.table>

        {{-- hidden 検索一覧用--}}
        <x-input.hidden id="student_id" :editData=$editData />

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['student_id'])}}" />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.desired_mng-modal')

@stop