@extends('adminlte::page')

@section('title', '特別期間講習 日程連絡一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">特別期間名</th>
            <th>校舎</th>
            <th width="15%">受付開始日</th>
            <th width="15%">ステータス</th>
            <th width="15%">連絡日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="特別期間名">@{{item.year}}年@{{item.season_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">@{{item.campus_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="受付開始日">@{{$filters.formatYmd(item.s_start_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">@{{item.regstatus_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="連絡日">@{{$filters.formatYmd(item.apply_date)}}</x-bs.td-sp>
            <td>
                {{-- 未登録の場合（登録期間内の場合のみ押下可） --}}
                <div v-show="item.regist_status=={{ App\Consts\AppConst::CODE_MASTER_5_0 }}">
                    <x-button.list-edit caption="登録" vueHref="'{{ route('season_student-edit', '') }}/' + item.season_student_id"
                        vueDisabled="!item.s_end_date || $filters.formatYmdNoH(item.s_end_date) < {{$todayYmd}}" />
                </div>
                {{-- 登録済の場合 --}}
                <div v-show="item.regist_status=={{ App\Consts\AppConst::CODE_MASTER_5_1 }}">
                    <x-button.list-dtl caption="詳細" vueHref="'{{ route('season_student-detail', '') }}/' + item.season_student_id"/>
                </div>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop