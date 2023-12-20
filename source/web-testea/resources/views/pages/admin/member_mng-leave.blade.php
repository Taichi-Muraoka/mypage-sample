@extends('adminlte::page')

@section('title', '会員退会登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('parent_page', route('member_mng-detail', $editData['student_id']))

@section('parent_page_title', '生徒カルテ')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の会員について、退会登録を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">生徒ID</th>
            <td>{{$editData['student_id']}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$editData['student_name']}}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="退会日" id="leave_date" :editData=$editData />

    <x-input.textarea caption="退会理由・やり取りの記録等" id="memo" :editData=$editData />

    <x-input.date-picker caption="対応日" id="received_date" :editData=$editData />

    <x-bs.callout title="退会登録時の注意事項" type="danger">
        登録された退会日以降の生徒スケジュールが削除されます。<br>
        画面からの復元はできませんのでご注意ください。<br>
        対象生徒のアカウントは退会日以降、ログイン不可となります。<br>
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', $editData['student_id'])}}" />

            <div class="d-flex justify-content-end">
                <x-button.submit-edit caption="登録" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop