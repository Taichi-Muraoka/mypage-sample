@extends('adminlte::page')

@section('title', 'サンプル一覧２（モーダルフォーム有）')

@section('content')

{{-- 検索フォーム --}}
{{-- 検索条件を保持・引き継ぐ場合は :initSearchCond=true を付ける --}}
<x-bs.card :search=true :initSearchCond=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- 生徒リスト 検索Boxを表示する (select2Search=true) --}}
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$students :editData=$editData
                :rules=$rules :select2Search=true :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            {{-- ステータスや種別は、検索Boxを非表示とする (select2Search=false) --}}
            <x-input.select id="sample_state" caption="ステータス" :select2=true :mastrData=$sampleStateList :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            {{-- 文字列検索（部分一致）--}}
            <x-input.text caption="サンプル件名" id="sample_title" :rules=$rules :editData=$editData />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('sample2_mng-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">登録日</th>
            <th width="15%">生徒名</th>
            <th>サンプル件名</th>
            <th width="15%">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- MEMO: 日付フォーマットを指定する --}}
            <td>@{{$filters.formatYmd(item.regist_date)}}</td>
            <td>@{{item.sname}}</td>
            <td>@{{item.sample_title}}</td>
            <td>@{{item.sample_state_name}}</td>
            <td>
                {{-- 詳細モーダル モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.sample_id']" />
                {{-- モーダルフォーム --}}
                <x-button.list-dtl caption="モーダル更新" dataTarget="#modal-dtl-input" :vueDataAttr="['id' => 'item.sample_id']" />
                {{-- 編集画面 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('sample2_mng-edit', '') }}/' + item.sample_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.sample2_mng-modal')
{{-- フォームモーダル --}}
@include('pages.admin.modal.sample2_mng_input-modal', ['modal_id' => 'modal-dtl-input', 'modal_form' => true, 'modal_button_id' => 'modal-input-buttons', 'form_center' => true, 'caption_OK' => '登録'])

@stop
