@extends('adminlte::page')

@section('title', '振替授業調整一覧')

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
            <x-input.select id="approval_status" caption="ステータス" :select2=true :mastrData=$statusList :select2Search=false
            :select2Search=false :blank=true />
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

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_check-new') }}" :small=true caption="振替授業登録" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>申請者種別</th>
            <th>校舎</th>
            <th>授業日・時限</th>
            <th>コース</th>
            <th>生徒名</th>
            <th>講師名</th>
            <th>当月依頼回数</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.apply_kind_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{$filters.formatYmdDay(item.target_date)}} @{{item.period_no}}限</td>
            <td>@{{item.course_name}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>@{{item.monthly_count}}</td>
            <td>@{{item.approval_status_name}}</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => 'item.transfer_apply_id']" />
                {{-- 承認 管理者承認待ち以外のときは非活性 --}}
                <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-approval"
                    :vueDataAttr="['id' => 'item.transfer_apply_id']"
                    vueDisabled="item.approval_status!={{ App\Consts\AppConst::CODE_MASTER_3_0 }}" />
                {{-- 編集 URLとIDを指定。承認・管理者調整済のときは非活性 --}}
                <x-button.list-edit vueHref="'{{ route('transfer_check-edit', '') }}/' + item.transfer_apply_id"
                    vueDisabled="item.approval_status=={{ App\Consts\AppConst::CODE_MASTER_3_2 }} || item.approval_status=={{ App\Consts\AppConst::CODE_MASTER_3_5 }}" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.transfer_check-modal')
{{-- モーダル(送信確認モーダル) 承認 --}}
@include('pages.admin.modal.transfer_check_approval-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-approval', 'caption_OK' => '承認', 'ok_icon' => 'fas fa-paper-plane'])

@stop
