@extends('adminlte::page')

@section('title', '会員情報詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>

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
    <x-bs.table :button=true class="inner-card">

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
            <td>{{$regular[$i]->startdate->format('Y/m/d')}}</td>
            <td>{{$regular[$i]->enddate->format('Y/m/d')}}</td>
            <td class="t-price">{{number_format($regular[$i]->tuition)}}</td>
            <td>{{$regular[$i]->regular_summary}}</td>
            @php
            $ids = ['roomcd' => $regular[$i]->roomcd, 'seq' => $regular[$i]->r_seq, 'sid' => $regular[$i]->sid];
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
    <x-bs.table :button=true class="inner-card">

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
            <td>{{$home_teacher_std[$i]->startdate->format('Y/m/d')}}</td>
            <td>{{$home_teacher_std[$i]->enddate->format('Y/m/d')}}</td>
            <td class="t-price">{{number_format($home_teacher_std[$i]->tuition)}}</td>
            <td>{{$home_teacher_std[$i]->std_summary}}</td>
            @php
            $ids = ['roomcd' => $home_teacher_std[$i]->roomcd, 'seq' => $home_teacher_std[$i]->std_seq, 'sid' => $home_teacher_std[$i]->sid];
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
    <x-bs.table :button=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">教室</th>
            <th width="15%">講習料</th>
            <th>講習名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($extra_individual); $i++) <tr>
            <td>{{$extra_individual[$i]->room_name}}</td>
            <td class="t-price">{{number_format($extra_individual[$i]->price)}}</td>
            <td>{{$extra_individual[$i]->name}}</td>
            @php
            $ids = ['roomcd' => $extra_individual[$i]->roomcd, 'seq' => $extra_individual[$i]->i_seq, 'sid' => $extra_individual[$i]->sid];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-course" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>

</x-bs.card>

{{-- モーダル --}}
{{-- 規定情報 --}}
@include('pages.admin.modal.member_mng_regulation-modal', ['modal_id' => 'modal-dtl-regulation'])
{{-- 家庭教師標準情報 --}}
@include('pages.admin.modal.member_mng_tutor-modal', ['modal_id' => 'modal-dtl-tutor'])
{{-- 短期個別講習 --}}
@include('pages.admin.modal.member_mng_course-modal', ['modal_id' => 'modal-dtl-course'])

@stop