@extends('adminlte::page')

@section('title','追加授業申請編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の追加授業申請について、変更を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>希望内容</th>
            <td>定期テスト対策で来週１コマ追加したい （英語）<br>
                2023/3/1の5限か6限希望</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- <x-input.select id="changes_state" caption="ステータス" :select2=true :select2Search=false :editData=$editData
        :mastrData=$statusList /> --}}
    <x-input.select caption="ステータス" id="state" :select2=true :editData=$editData>
        <option value="1">未対応</option>
        <option value="2">受付</option>
        <option value="3">対応済</option>
    </x-input.select>

    <x-input.textarea caption="事務局コメント" id="comment" :editData=$editData :rules=$rules />

    <x-input.hidden id="change_id" :editData=$editData />

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