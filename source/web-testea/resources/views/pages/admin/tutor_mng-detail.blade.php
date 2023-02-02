@extends('adminlte::page')

@section('title', '教師情報詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- カード --}}
<x-bs.card :form="true">

    {{-- hidden 削除用--}}
    <x-input.hidden id="tid" :editData=$editData />

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">教師No</th>
            <td>{{$extRirekisho->tid}}</td>
        </tr>
        <tr>
            <th>教師名</th>
            <td>{{$extRirekisho->name}}</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td>{{$extRirekisho->email}}</td>
        </tr>
    </x-bs.table>

    <x-bs.callout title="教師削除時の注意事項" type="danger">
        教師の削除を行うと、対象教師のアカウントがロックされ、対象教師に関連する情報が削除されます。<br>
        画面からの復元はできませんのでご注意ください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-delete />
        </div>
    </x-slot>

</x-bs.card>

@stop