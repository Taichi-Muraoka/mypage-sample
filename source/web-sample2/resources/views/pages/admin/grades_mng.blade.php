@extends('adminlte::page')

@section('title', '生徒成績一覧')

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

        {{-- hidden 検索一覧用--}}
        <x-input.hidden id="student_id" :editData=$editData />

        {{-- テーブル --}}
        <x-bs.table :button=true>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th class="t-minimum">登録日</th>
                <th width="20%">生徒名</th>
                <th width="15%">種別</th>
                <th>学期・試験名</th>
                <th></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                <td>@{{$filters.formatYmd(item.regist_date)}}</td>
                <td>@{{item.student_name}}</td>
                <td>@{{item.exam_type_name}}</td>
                <td>@{{item.practice_exam_name}} @{{item.regular_exam_name}} @{{item.term_name}}</td>
                <td>
                    {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                    <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                    <x-button.list-edit vueHref="'{{ route('grades_mng-edit', '') }}/' + item.id" />
                </td>
            </tr>
        </x-bs.table>
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
@include('pages.admin.modal.grades_mng-modal')

@stop