@extends('adminlte::page')

@section('title', (request()->routeIs('grades-edit')) ? '生徒成績編集' : '生徒成績登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の成績の{{(request()->routeIs('grades-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('grades-edit'))
    <x-bs.form-title>成績登録時の学年</x-bs.form-title>
    <p class="edit-disp-indent">{{$grade->name}}</p>
    @endif

    <x-input.select id="exam_type" caption="試験種別" :select2=true onChange="selectChangeGetCount" :mastrData=$examTypeList :editData=$editData
        :select2Search=false :blank=false />

    <x-input.text caption="模擬試験名" id="practice_exam_name" :rules=$rules :editData=$editData
        vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_0 }}" />

    <x-input.select id="regular_exam_cd" caption="定期考査名" :select2=true :mastrData=$teikiList :editData=$editData
        :select2Search=false :blank=true vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_1 }}" />

    <x-input.date-picker caption="試験日（開始日）" id="exam_date" :editData=$editData
        vShow="form.exam_type != {{ App\Consts\AppConst::CODE_MASTER_43_2 }}" />

    <x-input.select id="term_cd" caption="学期" :select2=true :mastrData=$termList :editData=$editData
        :select2Search=false :blank=true vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_2 }}" />

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-bs.form-title>成績</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small">
        <x-slot name="thead">
            <td>教科</td>
            <td v-show="form.exam_type != {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">得点</td>
            <td v-show="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_0 }}">満点</td>
            <td v-show="form.exam_type != {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">平均点</td>
            <td v-show="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_0 }}">偏差値</td>
            <td v-show="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">評定値</td>
        </x-slot>

        @for ($i = 0; $i < 15; $i++)
            {{-- 生徒の学年に応じた入力欄数までループする --}}
            <tr v-cloak v-show="form.display_count > {{$i}}">
                <x-bs.td-sp caption="教科">
                    <x-input.select id="g_subject_cd_{{$i}}" :select2=true :mastrData=$subjectList
                        :editData=$editDataDtls[$i] :select2Search=false :blank=true />
                </x-bs.td-sp>

                <x-bs.td-sp caption="得点" vShow="form.exam_type != {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">
                    <x-input.text id="score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
                </x-bs.td-sp>

                <x-bs.td-sp caption="満点" vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_0 }}">
                    <x-input.text id="full_score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
                </x-bs.td-sp>

                <x-bs.td-sp caption="平均点" vShow="form.exam_type != {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">
                    <x-input.text id="average_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
                </x-bs.td-sp>

                <x-bs.td-sp caption="偏差値" vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_0 }}">
                    <x-input.text id="deviation_score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
                </x-bs.td-sp>

                <x-bs.td-sp caption="評定値" vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">
                    <x-input.text id="rating_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
                </x-bs.td-sp>
            </tr>
        @endfor
    </x-bs.table>

    <x-input.textarea caption="次回に向けての抱負" id="student_comment" :editData=$editData :rules=$rules />

    <x-bs.callout>
        次回に向けて、現状を受け止めることが大切です。<br>
        しっかり復習して、次回に繋げましょう！
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="score_id" :editData=$editData />
    <x-input.hidden id="student_id" :editData=$editData />
    <x-input.hidden id="grade_cd" :editData=$editData />
    <x-input.hidden id="school_kind" :editData=$displayCountData />
    <x-input.hidden id="display_count" :editData=$displayCountData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('grades-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop