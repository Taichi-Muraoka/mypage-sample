@extends('adminlte::page')

@section('title', (request()->routeIs('agreement_mng-edit')) ? '契約編集' : '契約登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

{{-- 編集画面の場合のみ、一覧を経由し四階層とする --}}
@if (request()->routeIs('agreement_mng-edit'))
@section('parent_page2', route('agreement_mng', $editData['sid']))
@section('parent_page_title2', '契約情報一覧')
@endif

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の生徒の契約情報の{{(request()->routeIs('agreement_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.form-title>生徒名</x-bs.form-title>
    <p class="edit-disp-indent">CWテスト生徒１</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="授業種別" id="lesson_kind" :select2=true>
        <option value="1">個別</option>
        <option value="2">集団</option>
    </x-input.select>

    <x-input.select caption="契約コース名" id="course_name" :select2=true vShow="form.lesson_kind == 1">
        <option value="1">個別指導 中学生コース（受験準備学年）</option>
    </x-input.select>
    <x-input.select caption="契約コース名" id="course_name" :select2=true vShow="form.lesson_kind == 2">
        <option value="2">集団授業 中学生 英語・数学総復習パック</option>
    </x-input.select>

    <x-input.date-picker caption="契約開始日" id="start_date" :editData=$editData />
    <x-input.date-picker caption="契約終了日" id="end_date" :editData=$editData />

    <x-bs.table vShow="form.course_name == 1" :hover=false :vHeader=true>
        <tr>
            <th>単価</th>
            <td>8,470</td>
        </tr>
        <tr>
            <th>回数</th>
            <td>4</td>
        </tr>
        <tr>
            <th>金額</th>
            <td>33,880</td>
        </tr>
    </x-bs.table>
    <x-bs.table vShow="form.course_name == 2" :hover=false :vHeader=true>
        <tr>
            <th>単価</th>
            <td>5,000</td>
        </tr>
        <tr>
            <th>回数</th>
            <td>10</td>
        </tr>
        <tr>
            <th>金額</th>
            <td>50,000</td>
        </tr>
    </x-bs.table>

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('agreement_mng-edit'))
            {{-- 編集時 --}}
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('agreement_mng', $editData['sid'])}}" />
            @else
            {{-- 登録時 --}}
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['sid'])}}" />
            @endif

            @if (request()->routeIs('agreement_mng-edit'))
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