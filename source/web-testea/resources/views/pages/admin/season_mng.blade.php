@extends('adminlte::page')

@section('title', '特別期間講習 講習情報一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">特別期間</th>
            <th>校舎</th>
            <th>講師受付開始日</th>
            <th>講師受付終了日</th>
            <th>生徒受付開始日</th>
            <th>生徒受付終了日</th>
            <th>状態</th>
            <th>コマ組み確定日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.year}}年@{{item.season_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{$filters.formatYmd(item.t_start_date)}}</td>
            <td>@{{$filters.formatYmd(item.t_end_date)}}</td>
            <td>@{{$filters.formatYmd(item.s_start_date)}}</td>
            <td>@{{$filters.formatYmd(item.s_end_date)}}</td>
            {{-- 確定状態 講師受付開始日設定済み かつ 受付開始日以降の場合 --}}
            <td v-show="item.t_start_date && $filters.formatYmdNoH(item.t_start_date) <= {{$todayYmd}}">@{{item.status_name}}</td>
            {{-- 確定状態 講師受付開始日未設定 または 受付開始日以前の場合 --}}
            <td v-show="!item.t_start_date || $filters.formatYmdNoH(item.t_start_date) > {{$todayYmd}}">@{{item.status_name_bef}}</td>
            <td>@{{$filters.formatYmd(item.confirm_date)}}</td>
            <td>
                {{-- 受付期間登録ボタン 生徒受付終了日設定済み かつ 受付終了日以降の場合はdisable --}}
                <x-button.list-edit vueHref="'{{ route('season_mng-edit', '') }}/' + item.season_mng_id"
                    caption="受付期間登録" vueDisabled="item.s_end_date && $filters.formatYmdNoH(item.s_end_date) < {{$todayYmd}}" />
                {{-- コマ組み確定ボタン 受付期間外の場合はdisable --}}
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm"
                    :vueDataAttr="['season_mng_id' => 'item.season_mng_id']"
                    vueDisabled="$filters.formatYmdNoH(item.s_start_date) > {{$todayYmd}} || $filters.formatYmdNoH(item.s_end_date) < {{$todayYmd}}" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>
{{-- モーダル(スケジュール確定) --}}
@include('pages.admin.modal.season_mng_confirm-modal', ['modal_send_confirm' => true, 'modal_id' =>'modal-exec-confirm'])

@stop