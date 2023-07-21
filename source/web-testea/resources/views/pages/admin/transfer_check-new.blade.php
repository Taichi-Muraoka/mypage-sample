@extends('adminlte::page')

@section('title', '振替情報登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="transfer_apply_id" :editData=$editData />

    <p>個別指導授業の振替スケジュール登録を行います。</p>

    <x-input.select caption="生徒名" id="student" :select2=true :editData="$editData">
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
    </x-input.select>

    <x-input.select caption="授業日・時限" id="id" :select2=true :editData="$editData">
        <option value="1">2023/01/30 3限</option>
        <option value="2">2023/01/30 4限</option>
        <option value="3">2023/01/31 2限</option>
    </x-input.select>

    <div v-cloak>
        <x-bs.table vShow="form.id" :hover=false :vHeader=true>
            <tr>
                <th>教室</th>
                <td>久我山</td>
            </tr>
            <tr>
                <th>コース</th>
                <td>個別指導コース</td>
            </tr>
            <tr>
                <th>教師名</th>
                <td>CWテスト教師１０１</td>
            </tr>
        </x-bs.table>
    </div>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.card>
        <x-input.date-picker caption="振替日" id="transfer_date1" />

        <x-input.select caption="時限" id="period1" :select2=true :select2Search=false :editData=$editData>
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
            <option value="8">8限</option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻（変更する場合）" id="start_time1" :rules=$rules />

    </x-bs.card>

    <x-input.select caption="講師名（変更する場合）" id="new_tid" :select2=true :editData="$editData">
        <option value="1">CWテスト教師１</option>
        <option value="2">CWテスト教師２</option>
    </x-input.select>

    <x-input.textarea caption="振替理由" id="transfer_reason" :rules=$rules />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        入力した振替授業のスケジュールが登録されます。<br>
        対象の生徒・講師へお知らせが通知されます。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 登録時 --}}
            <x-button.submit-new />

        </div>
    </x-slot>

</x-bs.card>

@stop