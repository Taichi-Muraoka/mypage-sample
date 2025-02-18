@extends('adminlte::page')

@section('title', '振替授業調整一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_student-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>申請者種別</th>
            <th>授業日・時限</th>
            <th>コース</th>
            <th>講師名</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="申請日">@{{$filters.formatYmd(item.apply_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="申請者種別">@{{item.apply_kind_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="授業日・時限">@{{$filters.formatYmdDay(item.target_date)}} @{{item.period_no}}限</x-bs.td-sp>
            {{-- <x-bs.td-sp caption="授業日・時限">@{{item.target_date}} @{{item.period_no}}限</x-bs.td-sp> --}}
            <x-bs.td-sp caption="コース">@{{item.course_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="講師名">@{{item.tutor_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">@{{item.approval_status_name_for_student}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.transfer_apply_id']" />
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('transfer_student-edit', '') }}/' + item.transfer_apply_id"
                    caption="承認" {{-- 申請者種別=講師 かつ 承認待ちのときは活性 --}}
                    vueDisabled="!(item.approval_status=={{ App\Consts\AppConst::CODE_MASTER_3_1 }} && item.apply_kind=={{ App\Consts\AppConst::CODE_MASTER_53_2 }})" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.student.modal.transfer_student-modal')

@stop