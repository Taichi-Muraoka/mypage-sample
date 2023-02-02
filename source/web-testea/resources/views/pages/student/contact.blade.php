@extends('adminlte::page')

@section('title', '問い合わせ一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('contact-new') }}" caption="問い合わせ" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">問い合わせ日</th>
            <th width="15%">宛先</th>
            <th>件名</th>
            <th class="t-minimum">回答日</th>
            <th class="t-minimum">回答者名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="問い合わせ日" class="t-minimum">@{{item.regist_time|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="宛先">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="件名">@{{item.title}}</x-bs.td-sp>
            <x-bs.td-sp caption="回答日" class="t-minimum">@{{item.answer_time|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="回答者名" class="t-minimum">@{{item.name}}</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.contact_id']" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.contact-modal')

@stop