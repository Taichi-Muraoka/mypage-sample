@extends('adminlte::page')

@section('title', '基本情報')

@section('content')

<x-bs.card :form=true>

    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">生徒ID</th>
            <td>{{$student->student_id}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$student->name}}</td>
        </tr>
        <tr>
            <th>生徒メールアドレス</th>
            <td>{{$student->email_stu}}</td>
        </tr>
        <tr>
            <th>保護者メールアドレス</th>
            <td>{{$student->email_par}}</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$student->grade_name}}</td>
        </tr>
        <tr>
            <th>所属校舎</th>
            <td>{{$str_campus_names}}</td>
        </tr>
        <tr>
            <th>所属学校（小）</th>
            <td>{{$student->school_e_name}}</td>
        </tr>
        <tr>
            <th>所属学校（中）</th>
            <td>{{$student->school_j_name}}</td>
        </tr>
        <tr>
            <th>所属学校（高）</th>
            <td>{{$student->school_h_name}}</td>
        </tr>
        <tr>
            <th>会員ステータス</th>
            <td>{{$student->status_name}}</td>
        </tr>
        <tr>
            <th>入会日</th>
            <td>
                @if(isset($student->enter_date))
                {{$student->enter_date->format('Y/m/d')}}
                @endif
            </td>
        </tr>
    </x-bs.table>

    <x-bs.form-title>バッジ付与情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>バッジ</th>
            <th width="15%">合計</th>
            <th width="10%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="バッジ">
                @for ($i = 0; $i < $badges['tens_place']; $i++)
                <img src="/img/gold.png" class="user-image"  width="60" height="60" alt="badge">
                @endfor
                @for ($i = 0; $i < $badges['ones_place']; $i++)
                <img src="/img/silver.png" class="user-image"  width="50" height="50" alt="badge">
                @endfor
            </x-bs.td-sp>
            <x-bs.td-sp caption="合計">{{$badges['total_badges']}}</x-bs.td-sp>
            <x-bs.td-sp>
                <x-button.list-dtl :dataAttr="['student_id' => $student->student_id]"/>
            </x-bs.td-sp>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>レギュラー授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">曜日</th>
            <th width="10%">時限</th>
            <th width="20%">校舎</th>
            <th width="20%">コース名</th>
            <th width="20%">講師名</th>
            <th width="20%">科目</th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($regular_classes); $i++)
            <tr>
                <x-bs.td-sp caption="曜日">{{$regular_classes[$i]->day_name}}</x-bs.td-sp>
                <x-bs.td-sp caption="時限">{{$regular_classes[$i]->period_no}}</x-bs.td-sp>
                <x-bs.td-sp caption="校舎">{{$regular_classes[$i]->campus_name}}</x-bs.td-sp>
                <x-bs.td-sp caption="コース名">{{$regular_classes[$i]->course_name}}</x-bs.td-sp>
                <x-bs.td-sp caption="講師名">{{$regular_classes[$i]->tutor_name}}</x-bs.td-sp>
                <x-bs.td-sp caption="科目">{{$regular_classes[$i]->subject_name}}</x-bs.td-sp>
            </tr>
        @endfor
    </x-bs.table>

</x-bs.card>

{{-- モーダル --}}
@include('pages.student.modal.agreement_badge-modal')

@stop