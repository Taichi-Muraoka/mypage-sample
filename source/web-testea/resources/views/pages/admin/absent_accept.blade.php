@extends('adminlte::page')

@section('title', '欠席申請一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="state" caption="ステータス" :select2=true :mastrData=$statusList />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="name" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text caption="講師名" id="tname" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">申請日</th>
            <th>生徒名</th>
            <th width="20%">授業日時</th>
            <th width="15%">校舎</th>
            <th>講師名</th>
            <th width="t-minimum">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.apply_time|formatYmd}}</td>
            <td>@{{item.sname}}</td>
            <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
            <td>@{{item.room_name}}</td>
            <td>@{{item.tname}}</td>
            <td>@{{item.status}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['absent_apply_id' => 'item.absent_apply_id']" />
                {{-- モーダルを開く詳細ボタンを使用する --}}
                <x-button.list-dtl caption="受付" btn="btn-primary" dataTarget="#modal-dtl-acceptance"
                    :vueDataAttr="['absent_apply_id' => 'item.absent_apply_id']"
                    vueDisabled="item.statecd != {{ App\Consts\AppConst::CODE_MASTER_1_0 }}" />
                <x-button.list-edit vueHref="'{{ route('absent_accept-edit', '') }}/' + item.absent_apply_id" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.absent_accept-modal')
{{-- モーダル(送信確認モーダル) --}}
@include('pages.admin.modal.absent_accept_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance'])


@stop