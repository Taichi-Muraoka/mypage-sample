@extends('adminlte::page')

@section('title', '授業報告書 コメント登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">授業日時</th>
            <td>{{$editData->lesson_date->format('Y/m/d')}} {{$editData->start_time->format('H:i')}}</td>
        </tr>
        <tr>
            <th>教室</th>
            <td>{{$editData->class_name}}</td>
        </tr>
        <tr>
            <th>教師名</th>
            <td>{{$editData->tname}}</td>
        </tr>
        <tr>
            <th>授業時間数</th>
            <td>{{$editData->r_minutes}}</td>
        </tr>
        <tr>
            <th>学習内容</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">{{$editData->content}}</td>
        </tr>
        <tr>
            <th>次回までの宿題</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">{{$editData->homework}}</td>
        </tr>
        <tr>
            <th>教師よりコメント</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">{{$editData->teacher_comment}}</td>
        </tr>
    </x-bs.table>

    <div class="mt-4"></div>

    <x-input.textarea caption="保護者よりコメント" id="parents_comment" :editData=$editData :rules=$rules />
    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-edit caption="送信" />
        </div>
    </x-slot>

</x-bs.card>

@stop