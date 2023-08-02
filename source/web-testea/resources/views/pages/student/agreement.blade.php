@extends('adminlte::page')

@section('title', '生徒情報')

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
            <th>生徒メールアドレス</th>
            <td>{{$student->email}}</td>
        </tr>
        <tr>
            <th>保護者メールアドレス</th>
            <td>parent0001@ap.jeez.jp</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$student->cls_name}}</td>
        </tr>
        <tr>
            <th>所属校舎</th>
            <td>久我山 日吉</td>
        </tr>
        <tr>
            <th>所属学校（小）</th>
            <td>千駄谷小学校</td>
        </tr>
        <tr>
            <th>所属学校（中）</th>
            <td>渋谷第一中学校</td>
        </tr>
        <tr>
            <th>所属学校（高）</th>
            <td></td>
        </tr>
        <tr>
            <th>会員ステータス</th>
            <td>入会</td>
        </tr>
        <tr>
            <th>入会日</th>
            <td>2020/04/01</td>
        </tr>
    </x-bs.table>

    {{----------------------------}}
    {{-- モック用 @if,@forなしver--}}
    {{----------------------------}}
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
            <x-bs.td-sp>
                @for ($i = 1; $i <= 1; $i++) 
                <img src="/img/gold.png" class="user-image"  width="60" height="60" alt="badge">
                @endfor
                @for ($i = 1; $i <= 9; $i++) 
                <img src="/img/silver.png" class="user-image"  width="50" height="50" alt="badge">
                @endfor
            </x-bs.td-sp>
            <x-bs.td-sp>19</x-bs.td-sp>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-badge" />
            </td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>レギュラー授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">曜日</th>
            <th width="10%">時限</th>
            <th width="10%">校舎</th>
            <th width="20%">コース名</th>
            <th width="20%">講師名</th>
            <th width="20%">科目</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>火</td>
            <td>6</td>
            <td>久我山</td>
            <td>個別指導コース</td>
            <td>CWテスト講師１０１</td>
            <td>英語</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- <x-bs.form-title>特別期間講習情報</x-bs.form-title> --}}

    {{-- テーブル --}}
    {{-- <x-bs.table :button=true :smartPhone=true class="inner-card"> --}}
        {{-- テーブルタイトル行 --}}
        {{-- <x-slot name="thead">
            <th>特別期間名</th>
            <th width="20%">回数</th>
        </x-slot> --}}

        {{-- テーブル行 --}}
        {{-- <tr>
            <x-bs.td-sp caption="特別期間名">2023年 春期</x-bs.td-sp>
            <x-bs.td-sp caption="回数" class="t-price">4</x-bs.td-sp>
        </tr>
        <tr>
            <x-bs.td-sp caption="特別期間名">2022年 冬期</x-bs.td-sp>
            <x-bs.td-sp caption="回数" class="t-price">4</x-bs.td-sp>
        </tr>
    </x-bs.table> --}}


    {{------------}}
    {{-- 本番用 --}}
    {{------------}}
    {{-- @if(count($regular) > 0)
    <x-bs.form-title>契約情報</x-bs.form-title> --}}

    {{-- テーブル --}}
    {{-- <x-bs.table :button=true :smartPhone=true class="inner-card"> --}}
        {{-- テーブルタイトル行 --}}
        {{-- <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">月額</th>
            <th>契約情報</th>
            <th></th>
        </x-slot> --}}

        {{-- テーブル行 --}}
        {{-- @for ($i = 0; $i < count($regular); $i++) <tr>
            <x-bs.td-sp caption="開始日">{{$regular[$i]->startdate->format('Y/m/d')}}</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">{{$regular[$i]->enddate->format('Y/m/d')}}</x-bs.td-sp>
            <x-bs.td-sp caption="月額" class="t-price">{{number_format($regular[$i]->tuition)}}</x-bs.td-sp>
            <x-bs.td-sp caption="契約情報">{{$regular[$i]->regular_summary}}</x-bs.td-sp>
            @php
            $ids = ['roomcd' => $regular[$i]->roomcd, 'r_seq' => $regular[$i]->r_seq];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-regulation" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
    @endif --}}

    {{-- @if(count($extra_individual) > 0) --}}
    {{-- 余白 --}}
    {{-- <div class="mb-3"></div> --}}

    {{-- <x-bs.form-title>特別期間講習情報</x-bs.form-title> --}}

    {{-- テーブル --}}
    {{-- <x-bs.table :button=true :smartPhone=true class="inner-card"> --}}
        {{-- テーブルタイトル行 --}}
        {{-- <x-slot name="thead">
            <th width="15%">校舎</th>
            <th width="15%">講習料</th>
            <th>講習名</th>
            <th></th>
        </x-slot> --}}

        {{-- テーブル行 --}}
        {{-- @for ($i = 0; $i < count($extra_individual); $i++) <tr>
            <x-bs.td-sp caption="校舎">{{$extra_individual[$i]->room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="講習料" class="t-price">{{number_format($extra_individual[$i]->price)}}</x-bs.td-sp>
            <x-bs.td-sp caption="特別期間講習情報">{{$extra_individual[$i]->name}}</x-bs.td-sp>
            @php
            $ids = ['roomcd' => $extra_individual[$i]->roomcd, 'i_seq' => $extra_individual[$i]->i_seq];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-course" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
    @endif --}}

</x-bs.card>

{{-- モーダル --}}
{{-- バッジ情報 --}}
@include('pages.student.modal.agreement_badge-modal', ['modal_id' => 'modal-dtl-badge'])
{{--契約情報 現時点では不要のため削除予定 --}}
{{-- @include('pages.student.modal.agreement_regulation-modal', ['modal_id' => 'modal-dtl-regulation']) --}}
{{-- 特別期間講習 現時点では不要のため削除予定 --}}
{{-- @include('pages.student.modal.agreement_course-modal', ['modal_id' => 'modal-dtl-course']) --}}

@stop