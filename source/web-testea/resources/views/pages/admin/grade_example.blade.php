@extends('adminlte::page')

@section('title', '成績一覧')

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
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="school_kind" caption="学年" :select2=true onChange="selectChangeGetGrade"
                :mastrData=$schoolKindList :editData=$editData :select2Search=false :blank=true />

            <div v-cloak v-show="form.school_kind != ''">
                <x-input.select id="grade_cd" :select2=true :editData=$editData :select2Search=false :blank=false
                    multiple>
                    <option v-for="item in selectGetItemGrade.gradeList" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </div>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="exam_type" caption="種別" :select2=true onChange="selectChangeGetExam"
                :mastrData=$examTypeList :editData=$editData :select2Search=false :blank=true />

            <div v-cloak v-show="form.exam_type != '' && form.exam_type != {{AppConst::CODE_MASTER_43_0}}">
                <x-input.select id="exam_cd" :select2=true :editData=$editData :select2Search=false :blank=false
                    multiple>
                    <option v-for="item in selectGetItemExam.examList" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </div>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 From" id="date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 To" id="date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" vueDisabled="disabledBtnListExec"/>
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table>
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">登録日</th>
            <th>学年</th>
            <th>生徒名</th>
            <th>種別</th>
            <th>学期・試験名</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.regist_date)}}</td>
            <td>@{{item.grade_name}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.exam_type_name}}</td>
            <td>@{{item.practice_exam_name}} @{{item.regular_exam_name}} @{{item.term_name}}</td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル(送信確認モーダル) 出力 --}}
@include('pages.admin.modal.grade_example_output-modal', ['modal_send_confirm' => true])

@stop