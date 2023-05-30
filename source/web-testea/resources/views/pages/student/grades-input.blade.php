@extends('adminlte::page')

@section('title', (request()->routeIs('grades-edit')) ? '生徒成績編集' : '生徒成績登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の成績の{{(request()->routeIs('grades-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-input.select caption="種別" id="exam_type" :blank=false :select2=true :select2Search=false :rules=$rules :editData=$editData >
        <option value="1">模試</option>
        <option value="2">定期考査</option>
        <option value="3">通信票評定</option>
    </x-input.select>

    {{-- 模試 --}}
    <x-input.text caption="試験名" id="moshi_id" vShow="form.exam_type == 1"/>

    {{-- 定期考査 --}}
    <x-input.select caption="試験名" id="teiki_id" :select2=true :rules=$rules :editData=$editData
        vShow="form.exam_type == 2">
        <option value="1">1学期中間考査</option>
        <option value="2">1学期末考査</option>
        <option value="3">2学期中間考査</option>
        <option value="4">2学期末考査</option>
        <option value="5">3学期末考査</option>
    </x-input.select>

    {{-- 通信票評定 --}}
    <x-input.select caption="学期" id="term_id" :select2=true :rules=$rules
        vShow="form.exam_type == 3">
        <option value="1">1学期</option>
        <option value="2">2学期</option>
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
            <td>学年平均</td>
            <td>偏差値</td>
        </x-slot>

        <tr v-cloak>
            <x-bs.td-sp caption="教科">
                全教科合計
            </x-bs.td-sp>

            <x-bs.td-sp caption="得点">
                <x-input.text id="total_score"  :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="学年平均" class="not-center">
                <x-input.text id="total_average"  :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="偏差値" vShow="form.exam_type == 1">
                <x-input.text id="total_deviation"  :rules=$rules />
            </x-bs.td-sp>
        </tr>

        @for ($i = 0; $i < 10; $i++) <tr v-cloak>
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

            <x-bs.td-sp caption="学年平均" class="not-center">
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
            <td>学年平均</td>
        </x-slot>

        <tr v-cloak>
            <x-bs.td-sp caption="教科">
                全教科合計
            </x-bs.td-sp>

            <x-bs.td-sp caption="得点">
                <x-input.text id="total_score"  :rules=$rules />
            </x-bs.td-sp>

            <x-bs.td-sp caption="学年平均" class="not-center">
                <x-input.text id="total_average"  :rules=$rules />
            </x-bs.td-sp>
        </tr>

        @for ($i = 0; $i < 10; $i++) <tr v-cloak>
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

            <x-bs.td-sp caption="学年平均" class="not-center">
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

        @for ($i = 0; $i < 10; $i++) <tr v-cloak>
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

    <x-bs.callout>
        次回に向けて、現状を受け止めることが大切です。<br>
        しっかり復習して、次回に繋げましょう！
    </x-bs.callout>

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