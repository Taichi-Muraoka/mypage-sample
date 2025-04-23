@extends('adminlte::page')

@section('title', '講師退職処理')

{{-- 子ページ --}}
@section('child_page', true)

@section('parent_page', route('tutor_mng-detail', $editData['tutor_id']))

@section('parent_page_title', '講師情報')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の講師について、退職処理を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">講師ID</th>
            <td>{{$editData['tutor_id']}}</td>
        </tr>
        <tr>
            <th width="15%">講師名</th>
            <td>{{$tname}}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="退職日" id="leave_date" />

    <x-bs.callout title="講師退職処理の注意事項" type="danger">
        退職処理を行うと、対象講師のアカウントは退職日以降、ログイン不可となります。
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 講師情報に戻る --}}
            <x-button.back url="{{route('tutor_mng-detail', $editData['tutor_id'])}}" />

            <div class="d-flex justify-content-end">
                <x-button.submit-edit caption="登録" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop