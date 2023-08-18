@extends('adminlte::page')

@section('title', '生徒成績一覧')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $sid))

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
            <th width="15%">登録日</th>
            <th width="20%">生徒名</th>
            <th width="15%">種別</th>
            <th>学期・試験名</th>
            <th></th>
        </x-slot>

        {{-- モック用処理 --}}
        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="登録日">2023/07/21</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">CWテスト生徒１</x-bs.td-sp>
            <x-bs.td-sp caption="種別">通信票評定</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">１学期（前期）</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '3']" />
                <x-button.list-edit href="{{ route('grades_mng-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="登録日">2023/04/10</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">CWテスト生徒１</x-bs.td-sp>
            <x-bs.td-sp caption="種別">定期考査</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">１学期（前期）中間考査</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '2']" />
                <x-button.list-edit href="{{ route('grades_mng-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="登録日">2023/03/18</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">CWテスト生徒１</x-bs.td-sp>
            <x-bs.td-sp caption="種別">模試</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">全国統一模試</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '1']" />
                <x-button.list-edit href="{{ route('grades_mng-edit', 1) }}" />
            </td>
        </tr>

        {{-- 本番用処理 --}}
        {{-- テーブル行 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="登録日">@{{item.regist_time|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.sname}}</x-bs.td-sp>
            <x-bs.td-sp caption="試験種別">@{{item.type_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="試験名">@{{item.teiki_name}}@{{item.moshi_name}}</x-bs.td-sp>
            <td> --}}
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                {{-- <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                <x-button.list-edit vueHref="'{{ route('grades_mng-edit', '') }}/' + item.id" />
                </td>
        </tr> --}}

    </x-bs.table>

</x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('member_mng-detail', $sid)}}" />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
@include('pages.admin.modal.grades_mng-modal')

@stop