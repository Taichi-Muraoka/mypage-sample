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

        {{-- モック用処理 --}}
        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="登録日">2023/05/15</x-bs.td-sp>
            <x-bs.td-sp caption="種別">定期考査</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">１学期（前期）中間考査</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '1']" />
                <x-button.list-edit href="{{ route('grades-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="登録日">2023/03/28</x-bs.td-sp>
            <x-bs.td-sp caption="種別">模擬試験</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">春期全国統一模試</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '2']" />
                <x-button.list-edit href="{{ route('grades-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="登録日">2023/03/20</x-bs.td-sp>
            <x-bs.td-sp caption="種別">通信票評定</x-bs.td-sp>
            <x-bs.td-sp caption="学期・試験名">２学期（後期）</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '3']" />
                <x-button.list-edit href="{{ route('grades-edit', 1) }}" />
            </td>
        </tr>

        {{-- 本番用処理 --}}
        {{-- テーブル行 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="登録日">@{{item.regist_time|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="試験種別">@{{item.type_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="試験名">@{{item.teiki_name}}@{{item.moshi_name}}</x-bs.td-sp>
            <td> --}}
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                {{-- <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" /> --}}
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                {{-- <x-button.list-edit vueHref="'{{ route('grades-edit', '') }}/' + item.id" /> --}}
            {{-- </td>
        </tr> --}}
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.grades-modal')

@stop