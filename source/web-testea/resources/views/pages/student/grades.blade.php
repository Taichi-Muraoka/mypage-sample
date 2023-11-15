@extends('adminlte::page')

@section('title', '生徒成績一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('grades-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">登録日</th>
            <th width="15%">種別</th>
            <th>学期・試験名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="登録日">@{{$filters.formatYmd(item.regist_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="種別">@{{item.exam_type_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">@{{item.practice_exam_name}} @{{item.regular_exam_name}} @{{item.term_name}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                {{-- ボタンスペース --}}
                &nbsp;
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('grades-edit', '') }}/' + item.id" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.grades-modal')

@stop