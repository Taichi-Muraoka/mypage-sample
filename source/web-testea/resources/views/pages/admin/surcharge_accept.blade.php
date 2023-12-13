@extends('adminlte::page')

@section('title', '追加請求申請受付一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師名" :select2=true :mastrData=$tutorList :editData=$editData
                :select2Search=true :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="surcharge_kind" caption="請求種別" :select2=true :mastrData=$surchargeKindList
                :editData=$editData :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="approval_status" caption="ステータス" :select2=true :mastrData=$approvalStatusList
                :editData=$editData :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="payment_status" caption="支払状況" :select2=true :mastrData=$paymentStatusList
                :editData=$editData :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 From" id="apply_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 To" id="apply_date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>講師名</th>
            <th>請求種別</th>
            <th>校舎</th>
            <th>時間(分)</th>
            <th>金額</th>
            <th>ステータス</th>
            <th>支払年月</th>
            <th>支払状況</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>@{{item.surcharge_kind_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.minutes}}</td>
            <td class="t-price">@{{$filters.toLocaleString(item.tuition)}}</td>
            <td>@{{item.approval_status_name}}</td>
            <td>@{{$filters.formatYm(item.payment_date)}}</td>
            <td>@{{item.payment_status_name}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.surcharge_id']" />
                <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-acceptance"
                    :vueDataAttr="['id' => 'item.surcharge_id']" vueDisabled="item.disabled_btn" />
                <x-button.list-edit vueHref="'{{ route('surcharge_accept-edit', '') }}/' + item.surcharge_id"
                    vueDisabled="item.disabled_btn" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- 詳細モーダル --}}
@include('pages.admin.modal.surcharge_accept-modal')
{{-- 承認モーダル(送信確認モーダル) --}}
@include('pages.admin.modal.surcharge_accept_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance', 'caption_OK' => '承認'])

@stop