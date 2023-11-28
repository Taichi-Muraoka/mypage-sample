@extends('adminlte::page')

@section('title', '欠席申請一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :select2Search=false
                :blank=false />
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :select2Search=false />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="status" caption="ステータス" :select2=true :mastrData=$statusList :select2Search=false
                :blank=true />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$studentList :select2Search=true
                :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師名" :select2=true :mastrData=$tutorList :select2Search=true
                :blank=true />
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
            <th>授業日・時限</th>
            <th>校舎</th>
            <th>講師名</th>
            <th class="t-minimum">ステータス</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{$filters.formatYmdDay(item.target_date)}} @{{item.period_no}}限</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>@{{item.status_name}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['absent_apply_id' => 'item.absent_apply_id']" />
                {{-- ボタンスペース --}}
                &nbsp;
                <x-button.list-dtl caption="受付" btn="btn-primary" dataTarget="#modal-dtl-acceptance"
                    :vueDataAttr="['absent_apply_id' => 'item.absent_apply_id']"
                    vueDisabled="item.status == {{ App\Consts\AppConst::CODE_MASTER_1_1 }}" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.absent_accept-modal')
{{-- 送信確認モーダル --}}
@include('pages.admin.modal.absent_accept_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance'])

@stop