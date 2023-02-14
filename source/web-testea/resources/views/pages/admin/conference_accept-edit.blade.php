@extends('adminlte::page')

@section('title', '面談日程登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="conference_accept_id" :editData=$editData />

    <p>以下の面談日程希望をもとに、面談日程の登録を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">教室</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>第１希望日時</th>
            <td>2023/01/30 16:00</td>
        </tr>
        <tr>
            <th>第２希望日時</th>
            <td>2023/01/31 16:00</td>
        </tr>
        <tr>
            <th>第３希望日時</th>
            <td>2023/02/01 16:00</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="面談日" id="conference_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData/>

    <x-input.select caption="事務局ステータス" id="status" :select2=true :blank=false :editData="$editData">
        <option value="1" selected>未対応</option>
        <option value="2">対応済</option>
    </x-input.select>

    <x-bs.callout title="登録の際の注意事項" type="warning">
        ステータスを「対応済」として送信ボタンを押下すると、指定した日時で面談スケジュールが登録されます。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                {{-- <x-button.submit-delete /> --}}
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop