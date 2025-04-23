@extends('adminlte::page')

@section('title', '追加請求申請一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('surcharge-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">申請日</th>
            <th>請求種別</th>
            <th>実施日</th>
            <th>時間(分)</th>
            <th>金額</th>
            <th>ステータス</th>
            <th>支払年月</th>
            <th>支払状況</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="申請日">@{{$filters.formatYmd(item.apply_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="請求種別">@{{item.surcharge_kind_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="実施日">@{{$filters.formatYmd(item.working_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="時間(分)">@{{item.minutes}}</x-bs.td-sp>
            <x-bs.td-sp caption="金額" class="t-price">@{{$filters.toLocaleString(item.tuition)}}</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">@{{item.approval_status_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="支払年月">@{{$filters.formatYm(item.payment_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="支払状況">@{{item.payment_status_name}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.surcharge_id']" />
                <x-button.list-edit vueHref="'{{ route('surcharge-edit', '') }}/' + item.surcharge_id"
                    vueDisabled="item.disabled_btn" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.surcharge-modal')

@stop