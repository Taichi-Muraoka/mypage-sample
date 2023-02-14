@extends('adminlte::page')

@section('title', (request()->routeIs('transfer_student-edit')) ? '振替日承認' : '振替希望日連絡')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('transfer_student-edit'))
    {{-- 編集時 --}}
    <p>以下の授業振替希望について、承認を行います。承認ステータスを選択してください。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">教師名</th>
            <td>教師１</td>
        </tr>
        <tr>
            <th>授業日時</th>
            <td>2023/01/30 4限 15:00</td>
        </tr>
        <tr>
            <th>振替日時</th>
            <td>2023/02/06 4限 15:00</td>
        </tr>
        <tr>
            <th>振替理由</th>
            <td>私用都合のため</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="承認ステータス" id="transfer_id" :select2=true :editData="$editData">
        <option value="1" selected>承認待ち</option>
        <option value="2">承認</option>
        <option value="3">却下</option>
    </x-input.select>

    <x-input.textarea caption="コメント" id="transfer_comment" :rules=$rules />

    @else
    {{-- 登録時 --}}
    <p>授業の振替希望日連絡を行います。</p>

    <x-input.select caption="授業日・時限" id="id" :select2=true :editData="$editData">
        <option value="1">2023/01/30 3限</option>
        <option value="2">2023/01/30 4限</option>
        <option value="3">2023/01/31 2限</option>
    </x-input.select>

    <x-input.date-picker caption="振替希望日" id="transfer_date" />

    <x-input.select caption="時限" id="transfer_time" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
        <option value="1">1限</option>
        <option value="2">2限</option>
        <option value="3">3限</option>
        <option value="4">4限</option>
        <option value="5">5限</option>
        <option value="6">6限</option>
        <option value="7">7限</option>
    </x-input.select>

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules />

    <x-input.textarea caption="振替理由" id="transfer_reason" :rules=$rules />

    @endif

    {{-- hidden --}}
    <x-input.hidden id="transfer_student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('transfer_student-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                {{-- 削除機能なし --}}
                {{-- <x-button.submit-delete /> --}}
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