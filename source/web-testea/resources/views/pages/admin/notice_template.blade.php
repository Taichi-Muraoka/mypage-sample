@extends('adminlte::page')

@section('title', 'お知らせ定型文一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('notice_template-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">表示順</th>
            <th width="15%">定型文名</th>
            <th>タイトル</th>
            <th width="15%">種別</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.order_code}}</td>
            <td>@{{item.template_name}}</td>
            <td>@{{item.title}}</td>
            <td>@{{item.type_name}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['template_id' => 'item.template_id']" />
                {{-- スペース --}}
                &nbsp;
                <x-button.list-edit vueHref="'{{ route('notice_template-edit', '') }}/' + item.template_id" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
{{-- 詳細 --}}
@include('pages.admin.modal.notice_template-modal')

@stop