@extends('adminlte::page')

@section('title', '授業報告書一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- 校舎リスト選択時、onChangeによる生徒リストの絞り込みを行う。-1の場合は自分の受け持ちの生徒だけに絞り込み --}}
            <x-input.select caption="校舎" id="campus_cd" :select2=true onChange="selectChangeGetRoom" :editData=$editData
                :mastrData=$rooms :select2Search=false :blank=true emptyValue="-1" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="生徒名" id="student_id" :select2=true :editData=$editData>
                {{-- vueで動的にプルダウンを作成 --}}
                <option v-for="item in selectGetItem.selectItems" :value="item.id">
                    @{{ item.value }}
                </option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="course_cd" caption="コース" :select2=true :mastrData=$courses :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="approval_status" caption="承認ステータス" :select2=true :mastrData=$statusList :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('report_regist-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">授業日・時限</th>
            <th>校舎</th>
            <th>コース</th>
            <th>講師名</th>
            <th>生徒名</th>
            <th width="15%">承認ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="授業日・時限">@{{$filters.formatYmdDay(item.lesson_date)}} @{{item.period_no}}限</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="コース">@{{item.course_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="講師名">@{{item.tutor_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.student_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="承認ステータス">@{{item.status_name}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                <x-button.list-edit vueHref="'{{ route('report_regist-edit', '') }}/' + item.id" 
                {{-- 承認のときは非活性 --}}
                vueDisabled="item.approval_status == {{ App\Consts\AppConst::CODE_MASTER_4_2 }}"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.report_regist-modal')

@stop