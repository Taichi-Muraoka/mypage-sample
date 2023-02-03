@extends('adminlte::page')

@section('title', '季節講習のコマ組み')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>季節講習のスケジュール登録を行います。教室・生徒・担当教師・特別期間を選択してください。</p>

    <x-input.select caption="教室" id="roomcd" :select2=true :editData=$editData>
        <option value="1">仙台駅前</option>
        <option value="2">定禅寺</option>
        <option value="3">長町南</option>
    </x-input.select>

    <x-input.select caption="生徒名" id="sid" :select2=true :editData=$editData>
        <option value="1">CWテスト生徒１</option>
        <option value="2">CWテスト生徒２</option>
        <option value="3">CWテスト生徒３</option>
        <option value="4">CWテスト生徒４</option>
        <option value="5">CWテスト生徒５</option>
    </x-input.select>

    <x-input.select caption="教師名" id="tid" :select2=true :editData=$editData>
        <option value="1">CWテスト教師１０１</option>
        <option value="2">CWテスト教師１０２</option>
    </x-input.select>

    <x-input.select caption="特別期間" id="season_kind" :select2=true :editData=$editData>
        <option value="1">22年度冬期</option>
        <option value="2">23年度春期</option>
    </x-input.select>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>登録可能な空コマ一覧</x-bs.form-title>

    <p class="text-muted" v-show="form.roomcd == '' || form.sid == '' || form.tid == '' || form.season_kind == ''">教室・生徒名・教師名・特別期間を選択してください</p>

    {{-- テーブル --}}
    <x-bs.table class="mb-3" v-show="form.roomcd != '' && form.sid != '' && form.tid != '' && form.season_kind != ''">
        <x-slot name="thead">
            <th width="5%"></th>
            <th>授業日</th>
            <th width="15%">時限</th>
            <th>開始時間</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td><x-input.checkbox id="20221226_1" name="shift" icheck=true value="20221226_1" :icheck=false /></td>
            <td>2022/12/26</td>
            <td>1</td>
            <td>09:00</td>
        </tr>
        <tr>
            <td><x-input.checkbox id="20221227_2" name="shift" icheck=true value="20221226_2" :icheck=false /></td>
            <td>2022/12/27</td>
            <td>2</td>
            <td>10:45</td>
        </tr>
        <tr>
            <td><x-input.checkbox id="20221228_3" name="shift" icheck=true value="20221228_3" :icheck=false /></td>
            <td>2022/12/28</td>
            <td>3</td>
            <td>13:15</td>
        </tr>
        <tr>
            <td><x-input.checkbox id="20221228_4" name="shift" icheck=true value="20221228_4" :icheck=false /></td>
            <td>2022/12/28</td>
            <td>4</td>
            <td>15:00</td>
        </tr>
        <tr>
            <td><x-input.checkbox id="20221228_5" name="shift" icheck=true value="20221228_5" :icheck=false /></td>
            <td>2022/12/28</td>
            <td>5</td>
            <td>16:45</td>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop