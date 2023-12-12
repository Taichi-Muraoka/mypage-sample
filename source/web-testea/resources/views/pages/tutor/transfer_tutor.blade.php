@extends('adminlte::page')

@section('title', '振替授業調整一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                onChange="selectChangeGetRoom" :select2Search=false emptyValue="-1" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="生徒名" id="student_id" :select2=true :editData=$editData :select2Search=true>
                {{-- vueで動的にプルダウンを作成 --}}
                <option v-for="item in selectGetItem.selectItems" :value="item.id">
                    @{{ item.value }}
                </option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="ステータス" id="approval_status" :select2=true :mastrData=$statusList
                :select2Search=false :editData=$editData />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_tutor-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>申請者種別</th>
            <th>授業日・時限</th>
            <th>コース</th>
            <th>生徒名</th>
            <th>校舎</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="申請日">@{{$filters.formatYmd(item.apply_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="申請者種別">@{{item.apply_kind_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="授業日・時限">@{{$filters.formatYmdDay(item.target_date)}} @{{item.period_no}}限</x-bs.td-sp>
            <x-bs.td-sp caption="コース">@{{item.course_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.student_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="校舎名">@{{item.campus_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">@{{item.approval_status_name}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.transfer_apply_id']" />
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('transfer_tutor-edit', '') }}/' + item.transfer_apply_id"
                    caption="承認" {{-- 申請者種別=生徒 かつ 承認待ちのときは活性 --}}
                    vueDisabled="!(item.approval_status=={{ App\Consts\AppConst::CODE_MASTER_3_1 }} && item.apply_kind=={{ App\Consts\AppConst::CODE_MASTER_53_1 }})" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.transfer_tutor-modal')

@stop