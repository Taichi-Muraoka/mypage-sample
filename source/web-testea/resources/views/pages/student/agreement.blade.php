@extends('adminlte::page')

@section('title', '契約内容')

@section('content')


<x-bs.card :form=true>

    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">生徒No</th>
            <td>{{$student->sid}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$student->name}}</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td>{{$student->email}}</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$student->cls_name}}</td>
        </tr>
        <tr>
            <th>所属教室</th>
            <td>{{$roomcds}}</td>
        </tr>
    </x-bs.table>

    @if(count($regular) > 0)
    <x-bs.form-title>規定情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">月額</th>
            <th>規定情報</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($regular); $i++) <tr>
            <x-bs.td-sp caption="開始日">{{$regular[$i]->startdate->format('Y/m/d')}}</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">{{$regular[$i]->enddate->format('Y/m/d')}}</x-bs.td-sp>
            <x-bs.td-sp caption="月額" class="t-price">{{number_format($regular[$i]->tuition)}}</x-bs.td-sp>
            <x-bs.td-sp caption="規定情報">{{$regular[$i]->regular_summary}}</x-bs.td-sp>
            @php
            $ids = ['roomcd' => $regular[$i]->roomcd, 'r_seq' => $regular[$i]->r_seq];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-regulation" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

    @if(count($home_teacher_std) > 0)
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>家庭教師標準情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">月額</th>
            <th>家庭教師標準</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($home_teacher_std); $i++) <tr>
            <x-bs.td-sp caption="開始日">{{$home_teacher_std[$i]->startdate->format('Y/m/d')}}</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">{{$home_teacher_std[$i]->enddate->format('Y/m/d')}}</x-bs.td-sp>
            <x-bs.td-sp caption="月額" class="t-price">{{number_format($home_teacher_std[$i]->tuition)}}</x-bs.td-sp>
            <x-bs.td-sp caption="家庭教師標準">{{$home_teacher_std[$i]->std_summary}}</x-bs.td-sp>
            @php
            $ids = ['roomcd' => $home_teacher_std[$i]->roomcd, 'std_seq' => $home_teacher_std[$i]->std_seq];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-tutor" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

    @if(count($extra_individual) > 0)
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>短期個別講習情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">教室</th>
            <th width="15%">講習料</th>
            <th>講習名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($extra_individual); $i++) <tr>
            <x-bs.td-sp caption="教室">{{$extra_individual[$i]->room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="月額" class="t-price">{{number_format($extra_individual[$i]->price)}}</x-bs.td-sp>
            <x-bs.td-sp caption="短期個別講習">{{$extra_individual[$i]->name}}</x-bs.td-sp>
            @php
            $ids = ['roomcd' => $extra_individual[$i]->roomcd, 'i_seq' => $extra_individual[$i]->i_seq];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-course" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

</x-bs.card>

{{-- モーダル --}}
{{-- 規定情報 --}}
@include('pages.student.modal.agreement_regulation-modal', ['modal_id' => 'modal-dtl-regulation'])
{{-- 家庭教師標準情報 --}}
@include('pages.student.modal.agreement_tutor-modal', ['modal_id' => 'modal-dtl-tutor'])
{{-- 短期個別講習 --}}
@include('pages.student.modal.agreement_course-modal', ['modal_id' => 'modal-dtl-course'])

@stop