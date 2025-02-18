@extends('adminlte::page')

@section('title', '授業情報検索')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :rules=$rules :select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="student_name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="course_cd" caption="コース" :select2=true :mastrData=$courses :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="tutor_name" caption="講師名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="lesson_kind" caption="授業区分" :select2=true :mastrData=$lesson_kind :editData=$editData
                :rules=$rules :select2Search=false :blank=true/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="absent_status" caption="出欠ステータス" :select2=true :mastrData=$absent_status :editData=$editData
                :rules=$rules :select2Search=false :blank=true/>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="教科" id="subject_cd" :select2=true :mastrData=$subjects :editData=$editData
                :rules=$rules :select2Search=false :blank=true/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="授業報告書ステータス" id="report_status" :select2=true :mastrData=$report_status_list :editData=$editData
                :rules=$rules :select2Search=false :blank=true/>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="日付 From" id="target_date_from" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="日付 To" id="target_date_to" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">日付</th>
            <th>曜日</th>
            <th>時限/開始</th>
            <th>コース</th>
            <th>校舎</th>
            <th>生徒名</th>
            <th>講師名/担当者名</th>
            <th>教科</th>
            <th>授業区分</th>
            <th>出欠</th>
            <th>報告書</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.target_date)}}</td>
            <td>@{{$filters.formatWeek(item.target_date)}}</td>
            <td>
                <span v-if="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
                    @{{item.start_time}}
                </span>
                <span v-if="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
                    @{{(item.period_no)}}限
                </span>
            </td>
            <td>@{{(item.course_name)}}</td>
            <td>@{{(item.room_name)}}</td>
            <td>@{{(item.student_name)}}</td>
            <td>
                <span v-if="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
                    @{{item.admin_name}}
                </span>
                <span v-if="item.course_kind != {{ App\Consts\AppConst::CODE_MASTER_42_3 }}">
                    @{{(item.tutor_name)}}
                </span>
            </td>
            <td>@{{(item.subject_name)}}</td>
            <td>
                @{{(item.lesson_kind_name)}}
                <span v-show="item.create_kind == {{ App\Consts\AppConst::CODE_MASTER_32_2 }}">(@{{(item.create_kind_name)}})</span>
            </td>
            <td>
                <span v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">@{{(item.absent_status_name)}}</span>
            </td>
            <td>@{{item.report_status}}</td>
            <td><x-button.list-dtl :vueDataAttr="['id' => 'item.id']" /></td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.student_class-modal')

@stop
