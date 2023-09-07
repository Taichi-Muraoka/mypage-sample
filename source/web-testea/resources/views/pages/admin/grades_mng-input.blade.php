@extends('adminlte::page')

@section('title', (request()->routeIs('grades_mng-edit')) ? '生徒成績編集' : '生徒成績登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('grades_mng-edit'))
@section('parent_page2', route('grades_mng', $editData['sid']))
@section('parent_page_title2', '生徒成績一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の成績の{{(request()->routeIs('grades_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('grades_mng-edit'))
    <x-input.date-picker caption="登録日" id="regist_time" :editData=$editData />
    @endif

    <x-bs.form-title>生徒名</x-bs.form-title>
    {{-- <p class="edit-disp-indent">{{$editData->sname}}</p> --}}
    <p class="edit-disp-indent">CWテスト生徒１</p>

    @if (request()->routeIs('grades_mng-edit'))
    <x-bs.form-title>成績登録時の学年</x-bs.form-title>
    <p class="edit-disp-indent">中学1年</p>
    @endif

    {{-- <x-input.select caption="試験種別" id="exam_type" :blank=false :select2=true :select2Search=false :mastrData=$examTypes :rules=$rules :editData=$editData /> --}}
    <x-input.select caption="種別" id="exam_type" :blank=false :select2=true :select2Search=false :rules=$rules :editData=$editData >
        <option value="1">模試</option>
        <option value="2">定期考査</option>
        <option value="3">通信票評定</option>
    </x-input.select>

    {{-- 模試 --}}
    {{-- <x-input.select caption="試験名" id="moshi_id" :select2=true :mastrData=$moshiNames :rules=$rules :editData=$editData  vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_9_1 }}"/> --}}
    <x-input.text caption="試験名" id="moshi_id" vShow="form.exam_type == 1"/>

    {{-- 定期考査 --}}
    {{-- <x-input.select caption="試験名" id="teiki_id" :select2=true :mastrData=$teikiNames :rules=$rules :editData=$editData  vShow="form.exam_type == {{ App\Consts\AppConst::CODE_MASTER_9_2 }}"/> --}}
    <x-input.select caption="試験名" id="teiki_id" :select2=true :rules=$rules :editData=$editData
        vShow="form.exam_type == 2">
        <option value="1">1学期(前期)中間考査</option>
        <option value="2">1学期(前期)末考査</option>
        <option value="3">2学期(後期)中間考査</option>
        <option value="4">2学期(後期)末考査</option>
        <option value="5">3学期末考査</option>
    </x-input.select>

    {{-- 模試 --}}{{-- 定期考査 --}}
    <x-input.date-picker caption="試験日（開始日）" id="exam_date" :editData=$editData vShow="form.exam_type != 3" />

    {{-- 通信票評定 --}}
    <x-input.select caption="学期" id="term_id" :select2=true :rules=$rules
        vShow="form.exam_type == 3">
        <option value="1">1学期（前期）</option>
        <option value="2">2学期（後期）</option>
        <option value="3">3学期</option>
        <option value="4">学年</option>
    </x-input.select>

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-bs.form-title>成績</x-bs.form-title>

    {{-- hidden --}}
    <x-input.hidden id="grades_id" :editData=$editData />

    {{-- テーブル（模試入力用） --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small"  vShow="form.exam_type == 1">
        <x-slot name="thead">
            <td>教科</td>
            <td>得点</td>
            <td>満点</td>
            <td>平均点</td>
            <td>偏差値</td>
        </x-slot>

        {{-- 小6、中高10項目用意する --}}
        @for ($i = 0; $i < 7; $i++) <tr v-cloak>
            {{-- hidden --}}
            <x-input.hidden id="grades_seq_{{$i}}" :editData=$editDataDtls[$i] />

            <x-bs.td-sp caption="教科">
                {{-- プルダウンselect2 --}}
                <x-input.select id="curriculumcd_{{$i}}" :select2=true
                    :mastrData=$curriculums :rules=$rules :editData=$editDataDtls[$i] >
                    <option>3教科合計</option>
                    <option>5教科合計</option>
                </x-input.select>
            </x-bs.td-sp>

            <x-bs.td-sp caption="得点">
                <x-input.text id="score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="満点">
                <x-input.text id="full_score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="平均点" class="not-center">
                <x-input.text id="average_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="偏差値">
                <x-input.text id="deviation_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>
            </tr>
        @endfor
    </x-bs.table>

    {{-- テーブル（定期考査入力用 --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small"  vShow="form.exam_type == 2">
        <x-slot name="thead">
            <td>教科</td>
            <td>得点</td>
            <td>平均点</td>
        </x-slot>

        {{-- 中高15項目用意する --}}
        @for ($i = 0; $i < 15; $i++) <tr v-cloak>
            {{-- hidden --}}
            <x-input.hidden id="grades_seq_{{$i}}" :editData=$editDataDtls[$i] />

            <x-bs.td-sp caption="教科">
                {{-- プルダウンselect2 --}}
                <x-input.select id="curriculumcd_{{$i}}" :select2=true
                    :mastrData=$curriculums :rules=$rules :editData=$editDataDtls[$i] />
            </x-bs.td-sp>

            <x-bs.td-sp caption="得点">
                <x-input.text id="score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="平均点" class="not-center">
                <x-input.text id="average_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>
            </tr>
        @endfor
    </x-bs.table>

    {{-- テーブル（評定入力用） --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small"  vShow="form.exam_type == 3">
        <x-slot name="thead">
            <td>教科</td>
            <td>評定値</td>
        </x-slot>

        {{-- 中高15項目用意する --}}
        @for ($i = 0; $i < 9; $i++) <tr v-cloak>
            {{-- hidden --}}
            <x-input.hidden id="grades_seq_{{$i}}" />

            <x-bs.td-sp caption="教科">
                {{-- プルダウンselect2 --}}
                <x-input.select id="curriculumcd_{{$i}}" :select2=true
                    :mastrData=$curriculums :rules=$rules :editData=$editDataDtls[$i] />
            </x-bs.td-sp>

            <x-bs.td-sp caption="評定値">
                <x-input.text id="score_{{$i}}" :editData=$editDataDtls[$i] :rules=$rules />
            </x-bs.td-sp>

            </tr>
            @endfor
    </x-bs.table>

    <x-input.textarea caption="次回に向けての抱負" id="student_comment" :editData=$editData :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('grades_mng-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('grades_mng', $editData['sid'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
            @endif

            @if (request()->routeIs('grades_mng-edit'))
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