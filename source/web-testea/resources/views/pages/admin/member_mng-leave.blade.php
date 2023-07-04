@extends('adminlte::page')

@section('title', '会員退会登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('parent_page', route('member_mng-detail', 1))

@section('parent_page_title', '生徒カルテ')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の会員について、退会登録を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">生徒ID</th>
            <td>1</td>
        </tr>
        <tr>
            <th width="15%">生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th width="15%">会員ステータス</th>
            <td>在籍</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="退会日" id="leave_date" :editData=$editData />

    <x-input.textarea caption="退会理由・やり取りの記録等" id="karte_text" :editData=$editData />

    <x-input.date-picker caption="対応日" id="received_date" :editData=$editData />

    <x-bs.callout title="退会登録時の注意事項" type="danger">
        登録された退会日以降の生徒スケジュールが削除されます。<br>
        画面からの復元はできませんのでご注意ください。<br>
        対象生徒のアカウントは退会日以降、ログイン不可となります。<br>
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 生徒カルテに戻る --}}
            <x-button.back url="{{route('member_mng-detail', 1)}}" />

            <div class="d-flex justify-content-end">
                {{-- 削除機能なし --}}
                {{-- <x-button.submit-delete /> --}}
                <x-button.submit-edit caption="登録" />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop