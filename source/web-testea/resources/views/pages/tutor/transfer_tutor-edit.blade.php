@extends('adminlte::page')

@section('title','振替日承認')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業振替希望について、振替希望日一覧から１つ選択し、承認を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>授業日時</th>
            <td>2023/01/30 4限 15:00</td>
        </tr>
        <tr>
            <th>振替理由</th>
            <td>学校行事のため</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <p>振替希望日一覧</p>
    {{-- テーブル --}}
    <x-bs.table class="mb-3" v-show="form.roomcd != '' && form.sid != '' && form.tid != '' && form.season_kind != ''">
        <x-slot name="thead">
            <th width="5%"></th>
            <th width="20%">振替希望日</th>
            <th width="15%">時限</th>
            <th>開始時間</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td><x-input.radio id="20221226_1" name="shift" icheck=true value="20221226_1" :icheck=false /></td>
            <td>2023/02/03</td>
            <td>5</td>
            <td>16:00</td>
        </tr>
        <tr>
            <td><x-input.radio id="20221227_2" name="shift" icheck=true value="20221226_2" :icheck=false /></td>
            <td>2023/02/04</td>
            <td>6</td>
            <td>17:30</td>
        </tr>
        <tr>
            <td><x-input.radio id="20221228_3" name="shift" icheck=true value="20221228_3" :icheck=false /></td>
            <td>2023/02/06</td>
            <td>5</td>
            <td>16:00</td>
        </tr>
    </x-bs.table>

    <x-input.select caption="承認ステータス" id="transfer_id" :select2=true :editData="$editData">
        <option value="1" selected>承認待ち</option>
        <option value="2">承認</option>
        <option value="3">差戻し</option>
    </x-input.select>

    <x-input.textarea caption="コメント" id="transfer_comment" :rules=$rules />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        ステータスを「承認」として送信ボタンを押下すると、
        選択した振替日時で授業スケジュールが登録されます。
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="transfer_tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            <div class="d-flex justify-content-end">
                {{-- 削除機能なし --}}
                {{-- <x-button.submit-delete /> --}}
                {{-- 編集時 --}}
                <div class="d-flex justify-content-end">
                <x-button.submit-edit caption="登録" />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop