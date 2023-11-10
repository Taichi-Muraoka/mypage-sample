@extends('adminlte::page')

@section('title', '授業報告書一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
           <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false/>
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData onChange="selectChangeGetRoom"
                :select2Search=false emptyValue="-1"/>
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$grades :editData=$editData
                :select2Search=false />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="生徒名" id="student_id" :select2=true :editData=$editData>
                {{-- vueで動的にプルダウンを作成 --}}
                <option v-for="item in selectGetItem.selectItems" :value="item.id">
                    @{{ item.value }}
                </option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師" :select2=true :mastrData=$tutors :editData=$editData
                :select2Search=false />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="course_cd" caption="コース" :select2=true :mastrData=$courses :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="approval_status" caption="承認ステータス" :select2=true :mastrData=$statusList :editData=$editData
                :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業実施日 From" id="lesson_date_from" :editData=$editData/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業実施日 To" id="lesson_date_to" :editData=$editData/>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">登録日</th>
            <th>講師名</th>
            <th>授業日・時限</th>
            <th>校舎</th>
            <th>コース</th>
            <th>生徒名</th>
            <th>承認ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="登録日">@{{$filters.formatYmd(item.regist_date)}}</x-bs.td-sp>
            <x-bs.td-sp caption="講師名">@{{item.tutor_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="授業日・時限">@{{$filters.formatYmd(item.lesson_date)}} @{{item.period_no}}限</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="コース">@{{item.course_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">@{{item.student_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="承認ステータス">@{{item.status_name}}</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.id']" />
                {{-- スペース --}}
                &nbsp;
                {{-- <form action="{{ route('report_check-approval') }}" method="POST">
                    @csrf
                    <button type="submit" caption="承認" btn="btn-primary" class="btn btn-primary btn-sm"> --}}
                    <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-approval" :vueDataAttr="['id' => '2']" />
                    {{-- hidden --}}
                    {{-- <x-input.hidden id="id" />
                </form> --}}
                {{-- スペース --}}
                &nbsp;
                <x-button.list-edit vueHref="'{{ route('report_check-edit', '') }}/' + item.id"
                    {{-- 承認のときは非活性 --}}
                    vueDisabled="item.approval_status == {{ App\Consts\AppConst::CODE_MASTER_4_2 }}"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.report_check-modal')

@stop