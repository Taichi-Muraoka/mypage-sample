@extends('adminlte::page')

@section('title', '年間授業カレンダー情報取込')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>校舎毎に年間授業カレンダー情報の取込みを行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="30%">年度</th>
            <td>{{$school_year}}年度</td>
        </tr>
        <tr>
            <th width="30%">校舎</th>
            <td>{{$room_name}}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.file caption="年間業カレンダー情報ファイル" id="upload_file" />

    {{-- hidden --}}
    <x-input.hidden id="yearly_schedules_import_id" :editData=$editData />
    <x-input.hidden id="campus_cd" :editData=$editData />
    <x-input.hidden id="school_year" :editData=$editData />

    <x-bs.callout>
        ファイル形式：CSV形式
    </x-bs.callout>

    {{-- <x-bs.callout type="warning">
        送信ボタン押下後、バッググラウンドで処理されます。<br>
        (他の処理が実行中の場合は送信できません)<br>
        処理が正常に完了したかどうかは、下記の実行履歴よりご確認ください。
    </x-bs.callout> --}}

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop