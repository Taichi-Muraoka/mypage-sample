@extends('adminlte::page')

@section('title', '振替情報編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="transfer_apply_id" :editData=$editData />

    <p>以下の授業振替について、編集を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>申請者種別</th>
            <td>生徒</td>
        </tr>
        <tr>
            <th>教室</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th width="35%">生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>教師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
        <tr>
            <th>授業日時</th>
            <td>2023/01/30 4限 15:00</td>
        </tr>
        <tr>
            <th>振替理由</th>
            <td>学校行事のため</td>
        </tr>
        <tr>
            <th>ステータス</th>
            <td>承認</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="授業日・時限" id="transfer_date" :select2=true :blank=false :editData="$editData">
        <option value="1" selected>2023/02/06 4限</option>
        <option value="2">2023/02/07 4限</option>
        <option value="3">2023/02/07 5限</option>
    </x-input.select>

    <x-input.time-picker caption="振替授業開始日時" id="start_time" :rules=$rules :editData=$editData/>

    <x-bs.callout title="登録の際の注意事項" type="warning">
        ステータスが「承認」となっている場合、振替スケジュールが登録済みとなっています。
        変更登録の際はご注意ください。
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