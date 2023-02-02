@extends('adminlte::page')

@section('title', 'ギフトカード一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="card_state" caption="ステータス" :select2=true :mastrData=$statusList />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 From" id="apply_time_from" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 To" id="apply_time_to" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-exec caption="一覧出力" dataTarget="#modal-dtl-output" icon="fas fa-download" :small=true />
        <x-button.new href="{{ route('card_mng-new') }}" caption="ギフトカード付与" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">付与日</th>
            <th class="t-minimum">申請日</th>
            <th width="20%">生徒名</th>
            <th>ギフトカード名</th>
            <th width="20%">ステータス</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.grant_time|formatYmd}}</td>
            <td>@{{item.apply_time|formatYmd}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.card_name}}</td>
            <td>@{{item.status}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['card_id' => 'item.card_id']" />
                <x-button.list-dtl caption="受付" btn="btn-primary" :vueDataAttr="['card_id' => 'item.card_id']"
                    dataTarget="#modal-dtl-acceptance" vueDisabled="item.disabled" />
                <x-button.list-edit vueHref="'{{ route('card_mng-edit', '') }}/' + item.card_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.card_mng-modal')
{{-- モーダル(送信確認モーダル) 受付 --}}
@include('pages.admin.modal.card_mng_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance'])
{{-- モーダル(送信確認モーダル) 出力 --}}
@include('pages.admin.modal.card_mng_output-modal', ['modal_send_confirm' => true, 'modal_id' => 'modal-dtl-output'])

@stop