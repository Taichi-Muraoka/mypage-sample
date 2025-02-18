@extends('adminlte::page')

@section('title', '請求情報取込一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-href caption="更新" icon="fas fa-sync" :small=true btn="default" onClickPrevent="search" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>請求情報</th>
            <th width="15%">取込状態</th>
            <th width="15%">メール送信</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmString(item.invoice_date)}}分</td>
            <td><span v-if="item.import_state == {{ App\Consts\AppConst::CODE_MASTER_20_0 }}"
                    class="text-danger">@{{item.state_name}}</span><span v-else>@{{item.state_name}}</span></td>
            <td><span v-if="item.disabled_btn_mail == false"
                    class="text-danger">@{{item.mail_state_name}}</span><span v-else>@{{item.mail_state_name}}</span></td>
            <td>
                <x-button.list-send vueHref="'{{ route('invoice_import-import', '')}}/' + item.id" caption="取込" />
                <x-button.list-dtl caption="通知メール送信" btn="btn-primary" dataTarget="#modal-dtl-mail"
                    :vueDataAttr="['id' => 'item.id']"
                    vueDisabled="item.disabled_btn_mail" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>
{{-- モーダル(メール送信実行) --}}
@include('pages.admin.modal.invoice_import_mail-modal',
    ['modal_send_confirm' => true, 'modal_id' =>'modal-dtl-mail', 'ok_icon' => 'fas fa-paper-plane'])

@stop
