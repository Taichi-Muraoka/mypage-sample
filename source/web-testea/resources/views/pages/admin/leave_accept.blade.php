@extends('adminlte::page')

@section('title', '退会申請一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- @can('roomAdmin') --}}
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            {{-- <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="教室" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan --}}
            <x-input.select id="roomcd" caption="校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            {{-- <x-input.select id="leave_state" caption="ステータス" :select2=true :mastrData=$statusList /> --}}
            <x-input.select caption="ステータス" id="state" :select2=true :editData=$editData>
                <option value="1">未対応</option>
                <option value="2">受付</option>
                <option value="3">退会処理中</option>
                <option value="3">退会済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">申請日</th>
            <th>生徒名</th>
            <th width="15%">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.apply_time|formatYmd}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.status}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['leave_apply_id' => 'item.leave_apply_id']" />
                <x-button.list-dtl caption="受付" btn="btn-primary" dataTarget="#modal-dtl-acceptance"
                    :vueDataAttr="['leave_apply_id' => 'item.leave_apply_id']" {{-- 未対応のときだけ活性化 --}}
                    vueDisabled="item.leave_state != {{ App\Consts\AppConst::CODE_MASTER_5_0 }}" />
                <x-button.list-edit vueHref="'{{ route('leave_accept-edit', '') }}/' + item.leave_apply_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.leave_accept-modal')
{{-- モーダル(送信確認モーダル) --}}
@include('pages.admin.modal.leave_accept_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance'])

@stop