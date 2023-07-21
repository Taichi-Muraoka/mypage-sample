@extends('adminlte::page')

@section('title', '振替情報編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="transfer_apply_id" :editData=$editData />

    <p>以下の振替調整依頼について、振替または代講スケジュールを登録します。</p>
    <p>（管理者が振替を承認しない場合や、生徒－講師間で調整が難しい場合）</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">申請者種別</th>
            <td>生徒</td>
        </tr>
        <tr>
            <th>授業日・時限</th>
            <td>2023/01/30 4限 15:00</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>コース</th>
            <td>個別指導コース</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
        <tr>
            <th>振替希望日時１</th>
            <td>2023/02/03 5限</td>
        </tr>
        <tr>
            <th>振替希望日時２</th>
            <td>2023/02/04 6限</td>
        </tr>
        <tr>
            <th>振替希望日時３</th>
            <td>2023/02/06 5限</td>
        </tr>
        <tr>
            <th>振替理由／要望／連絡事項など</th>
            <td>学校行事のため</td>
        </tr>
        <tr>
            <th>ステータス</th>
            <td>承認待ち</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.card>
        {{-- 振替授業・代講授業の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="振替授業" id="transfer_type-1" name="transfer_type" value="1" :checked=true :editData=$editData />
            <x-input.radio caption="代講授業" id="transfer_type-2" name="transfer_type" value="2" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        {{-- 振替授業の場合 --}}
        <x-input.date-picker caption="振替日" id="transfer_date" vShow="form.transfer_type == 1" />

        <x-input.select caption="時限" id="period" :select2=true :select2Search=false :editData=$editData vShow="form.transfer_type == 1">
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻（変更する場合）" id="start_time" :rules=$rules vShow="form.transfer_type == 1"/>

        <x-input.select caption="講師名（変更する場合）" id="new_tid" :select2=true :editData="$editData" vShow="form.transfer_type == 1">
            <option value="1">CWテスト教師１</option>
            <option value="2">CWテスト教師２</option>
        </x-input.select>

        {{-- 代講授業の場合 --}}
        <x-input.select caption="代講講師名" id="daiko_tid" :select2=true :editData="$editData"  vShow="form.transfer_type == 2">
            <option value="1">CWテスト教師１</option>
            <option value="2">CWテスト教師２</option>
        </x-input.select>

    </x-bs.card>

    <x-bs.callout title="登録の際の注意事項" type="warning">
        入力した振替授業または代講授業のスケジュールが登録・更新されます。<br>
        対象の生徒・講師へお知らせが通知されます。
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