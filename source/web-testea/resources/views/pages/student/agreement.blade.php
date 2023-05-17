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
            <th>所属校舎</th>
            <td>{{$roomcds}}</td>
        </tr>
        <tr>
            <th>所属学校</th>
            <td>渋谷第一中学校</td>
        </tr>
        <tr>
            <th>入会日</th>
            <td>2020/04/01</td>
        </tr>
        <tr>
            <th>契約期間</th>
            <td>37 ヶ月</td>
        </tr>
    </x-bs.table>

    {{----------------------------}}
    {{-- モック用 @if,@forなしver--}}
    {{----------------------------}}
    <x-bs.form-title>バッジ付与情報</x-bs.form-title>

    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">バッジ</th>
            <td>
                @for ($i = 1; $i <= 2; $i++) 
                <img src="/img/gold.png" class="user-image"  width="50" height="50" alt="badge">
                @endfor
            </td>
        </tr>
    </x-bs.table>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">付与日</th>
            <th width="15%">校舎</th>
            <th>認定理由</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp>2023/05/10</x-bs.td-sp>
            <x-bs.td-sp>久我山</x-bs.td-sp>
            <x-bs.td-sp>生徒紹介（佐藤次郎さん）</x-bs.td-sp>
        </tr>
        <tr>
            <x-bs.td-sp>2023/04/01</x-bs.td-sp>
            <x-bs.td-sp>久我山</x-bs.td-sp>
            <x-bs.td-sp>契約期間が３年を超えた</x-bs.td-sp>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>契約情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">月額</th>
            <th>契約情報</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="開始日">2022/04/01</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">2023/03/31</x-bs.td-sp>
            <x-bs.td-sp caption="月額" class="t-price">16,390</x-bs.td-sp>
            <x-bs.td-sp caption="契約情報">月4回 60分 個別（中学1･2年生）料金</x-bs.td-sp>

            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-regulation" />
            </td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>特別期間講習情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">講習料</th>
            <th>講習名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="開始日">2023/07/24</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">2023/08/26</x-bs.td-sp>
            <x-bs.td-sp caption="講習料" class="t-price">40,970</x-bs.td-sp>
            <x-bs.td-sp caption="講習名">夏季特別期間講習</x-bs.td-sp>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-course"/>
            </td>
        </tr>
    </x-bs.table>


    {{------------}}
    {{-- 本番用 --}}
    {{------------}}
    @if(count($regular) > 0)
    <x-bs.form-title>契約情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">月額</th>
            <th>契約情報</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($regular); $i++) <tr>
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
    @endif

    @if(count($extra_individual) > 0)
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>特別期間講習情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true class="inner-card">
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">校舎</th>
            <th width="15%">講習料</th>
            <th>講習名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($extra_individual); $i++) <tr>
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
    @endif

</x-bs.card>

{{-- モーダル --}}
{{--契約情報 --}}
@include('pages.student.modal.agreement_regulation-modal', ['modal_id' => 'modal-dtl-regulation'])
{{-- 特別期間講習 --}}
@include('pages.student.modal.agreement_course-modal', ['modal_id' => 'modal-dtl-course'])

@stop