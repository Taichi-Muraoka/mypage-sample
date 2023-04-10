@extends('adminlte::page')

@section('title','生徒成績編集')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

@section('parent_page2', route('grades_mng', $editData['sid']))

@section('parent_page_title2', '生徒成績一覧')


@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の試験成績の変更を行います。</p>

    <x-input.date-picker caption="登録日" id="regist_time" :editData=$editData />

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->sname}}</p>

    <x-input.select caption="試験種別" id="exam_type" :blank=false :select2=true :select2Search=false :mastrData=$examTypes :rules=$rules :editData=$editData />

    {{-- 模試 --}}
    <x-input.select caption="試験名" id="moshi_id" :select2=true :mastrData=$moshiNames :rules=$rules :editData=$editData  vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_9_1 }}"/>

    {{-- 定期考査 --}}
    <x-input.select caption="試験名" id="teiki_id" :select2=true :mastrData=$teikiNames :rules=$rules :editData=$editData  vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_9_2 }}"/>
    {{-- hidden --}}
    <x-input.hidden id="grades_id" :editData=$editData />

    <x-bs.form-title>試験成績</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false>
        <x-slot name="thead">
            <td>教科</td>
            <td>得点</td>
            <td>前回比</td>
            <td>学年平均</td>
            <td>偏差値</td>
        </x-slot>

        @for ($i = 0; $i < 10; $i++)
        <tr v-cloak>
            {{-- hidden --}}
            <x-input.hidden id="grades_seq_{{$i}}" :editData=$editDataDtls[$i] />

            <x-bs.td-sp caption="教科">
                @if ($i < 5)
                    {{-- プルダウンselect2 --}}
                    <x-input.select id="curriculumcd_{{$i}}" :select2=true :mastrData=$curriculums :rules=$rules :editData=$editDataDtls[$i] />
                @else
                    {{-- フリー入力 --}}
                    <x-input.text id="curriculum_name_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
                @endif
            </x-bs.td-sp>

            <x-bs.td-sp caption="得点">
                <x-input.text id="score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="前回比">
                <x-input.select id="previoustime_{{$i}}" :blank=false :select2=true :select2Search=false :mastrData=$updownList :rules=$rules :editData=$editDataDtls[$i] />
            </x-bs.td-sp>

            <x-bs.td-sp caption="学年平均" class="not-center">
                <x-input.text id="average_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="偏差値">
                <x-input.text id="deviation_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>
        </tr>
        @endfor
    </x-bs.table>

    <x-input.textarea caption="次回の試験に向けての抱負" id="student_comment" :editData=$editData :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('grades_mng', $editData['sid'])}}" />

            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop