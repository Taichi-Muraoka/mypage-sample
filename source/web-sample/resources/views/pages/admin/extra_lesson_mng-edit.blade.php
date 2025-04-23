@extends('adminlte::page')

@section('title','追加授業依頼編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の追加授業依頼について、変更を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>{{$editData['campus_name']}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$editData['student_name']}}</td>
        </tr>
        <tr>
            <th>希望内容</th>
            <td class="nl2br">{{$editData['request']}}</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="ステータス" id="status" :select2=true :mastrData=$statusList :editData="$editData"
        :select2Search=false :blank=false />

    <x-input.textarea caption="管理者コメント" id="admin_comment" :editData=$editData :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="extra_apply_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop