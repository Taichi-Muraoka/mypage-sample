@extends('pages.common.modal')

@section('modal-body')

<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th width="35%">請求種別</th>
        <td>@{{item.surcharge_kind_name}}</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>実施日</th>
        <td>@{{$filters.formatYmd(item.working_date)}}</td>
    </tr>
    {{-- 請求種別 = サブコード8 時給のみ表示 --}}
    <tr v-show="item.sub_code == {{ App\Consts\AppConst::CODE_MASTER_26_SUB_8 }}">
        <th>開始時刻</th>
        <td>@{{item.start_time}}</td>
    </tr>
    <tr v-show="item.sub_code == {{ App\Consts\AppConst::CODE_MASTER_26_SUB_8 }}">
        <th>時間(分)</th>
        <td>@{{item.minutes}}</td>
    </tr>
    <tr>
        <th>金額</th>
        <td>@{{$filters.toLocaleString(item.tuition)}}</td>
    </tr>
    <tr>
        <th>内容(作業・費目等)</th>
        <td class="nl2br">@{{item.comment}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.approval_status_name}}</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td class="nl2br">@{{item.admin_comment}}</td>
    </tr>
    {{-- ステータス「承認」時のみ表示 --}}
    <tr v-show="item.approval_status == {{ App\Consts\AppConst::CODE_MASTER_2_1 }}">
        <th>支払年月</th>
        <td>@{{$filters.formatYm(item.payment_date)}}</td>
    </tr>
    <tr v-show="item.approval_status == {{ App\Consts\AppConst::CODE_MASTER_2_1 }}">
        <th>支払状況</th>
        <td>@{{item.payment_status_name}}</td>
    </tr>
</x-bs.table>

@overwrite