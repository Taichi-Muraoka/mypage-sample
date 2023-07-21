@extends('adminlte::page')

@section('title','振替依頼承認')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業振替依頼について、振替希望日一覧から１つ選択し、承認を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">授業日・時限</th>
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
            <th>講師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
        <tr>
            <th>振替理由／連絡事項など</th>
            <td>私用都合のため</td>
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
            <th> </th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td><x-input.radio id="20221226_1" name="shift" icheck=true value="20221226_1" :icheck=false /></td>
            <td>2023/02/03</td>
            <td>5</td>
            <td></td>
        </tr>
        <tr>
            <td><x-input.radio id="20221227_2" name="shift" icheck=true value="20221226_2" :icheck=false /></td>
            <td>2023/02/04</td>
            <td>6</td>
            <td></td>
        </tr>
        <tr style="background-color: #C0C0C0">
            <td><x-input.radio id="20221228_3" name="shift" icheck=true value="20221228_3" :icheck=false disabled==true /></td>
            <td>2023/02/06</td>
            <td>5</td>
            <td>空きブースなし</td>
        </tr>
    </x-bs.table>

    <x-input.select caption="ステータス" id="transfer_id" :select2=true :editData="$editData">
        <option value="1" selected>承認待ち</option>
        <option value="2">承認</option>
        <option value="3">差戻し</option>
    </x-input.select>

    <x-input.textarea caption="コメント" id="transfer_comment" :rules=$rules />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        振替希望日のいずれも都合が合わない場合は、コメント欄に理由を入力し、
        ステータスを「差戻」として送信してください。<br>
        ステータスを「承認」として送信すると、選択した振替日時で授業スケジュールが登録されます。
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="transfer_student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-edit caption="登録" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop