@extends('adminlte::page')

@section('title', '成績一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="school" caption="校舎" :select2=true>
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">本郷</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-bs.form-group name="notice_groups">
                <x-bs.form-title>学年</x-bs.form-title>
                {{-- 学年チェックボックス --}}
                @for ($i = 0; $i < count($noticeGroup); $i++)
                <x-input.checkbox :caption="$noticeGroup[$i]"
                        :id="'notice_group_' . $noticeGroup[$i]"
                        name="notice_groups" :value="$noticeGroup[$i]" />
                @endfor
            </x-bs.form-group>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="exam_kinds" caption="試験種別" :select2=true>
                <option value="1">模試</option>
                <option value="2">定期考査</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text caption="試験名" id="exam_name" />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="student_name" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text caption="講師名" id="teacher_name" />
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

    <x-bs.row>
        <x-bs.col2>
            <x-bs.form-group name="subject_groups">
                <x-bs.form-title>教科</x-bs.form-title>
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($subjectGroup); $i++)
                <x-input.checkbox :caption="$subjectGroup[$i]"
                        :id="'subject_group_' . $subjectGroup[$i]"
                        name="subject_groups" :value="$subjectGroup[$i]" />
                @endfor
            </x-bs.form-group>
        </x-bs.col2>
        <x-bs.col2>
            <x-bs.form-title>成績条件</x-bs.form-title>
            <x-input.radio caption="点数" id="score" name="kinds"
                value="1" :editData=$editData />
            <x-input.radio caption="偏差値" id="deviation" name="kinds"
                value="2" :editData=$editData />
            <x-input.text id="grade_conditions" />
            <x-input.radio caption="UP" id="up" name="conditions"
                value="1" :editData=$editData />
            <x-input.radio caption="DOWN" id="down" name="conditions"
                value="2" :editData=$editData />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>校舎</th>
            <th>学年</th>
            <th>試験種別</th>
            <th>試験名</th>
            <th>生徒名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>久我山</td>
            <td>中学2年</td>
            <td>模試</td>
            <td>全国統一模試</td>
            <td>CWテスト生徒１</td>
            <td>
                <x-button.list-dtl />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.grade_example-modal')

@stop