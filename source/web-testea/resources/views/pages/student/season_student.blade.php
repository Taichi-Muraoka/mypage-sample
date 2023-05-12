@extends('adminlte::page')

@section('title', '特別期間講習　日程連絡')

@section('content')

{{-- フォーム --}}
<x-bs.card class="pa-table-checked" :form=true >

  <p>下記の特別期間について、<b>ご都合の悪い日程・時間</b>を選択し、受講希望科目・回数を入力してください。</p>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
      <tr>
          <th class="t-minimum" width="25%">特別期間</th>
          <td>2023年春期</td>
      </tr>
      <tr>
          <th>受講回数（目安）</th>
          <td>4回</td>
      </tr>
      <tr>
        <th>提出締め切り</th>
        <td>3/10</td>
    </tr>
</x-bs.table>

            <x-input.select id="roomcd" caption="受講校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
            </x-input.select>

  {{-- チェックボックスのエラー時のメッセージ --}}
  <x-bs.form-group name="chkWs" />

  <x-bs.table :hover=false class="table-checked">

    {{-- テーブルタイトル行 --}}
    <x-slot name="thead">
      <th class="t-minimum t-week-time"></th>

      {{-- 曜日を表示 --}}
      <th class="t-week">3/27(月)</th>
      <th class="t-week">3/28(火)</th>
      <th class="t-week">3/29(水)</th>
      <th class="t-week">3/30(木)</th>
      <th class="t-week">3/31(金)</th>
      <th class="t-week">4/1(土)</th>
      <th class="t-week">4/3(月)</th>
      <th class="t-week">4/4(火)</th>
      <th class="t-week">4/5(水)</th>
      <th class="t-week">4/6(木)</th>
      <th class="t-week">4/7(金)</th>
      <th class="t-week">4/8(土)</th>
    </x-slot>

    {{-- 二重ループで組み立てる --}}
    @for ($i = 0; $i < count($timeList); $i++) <tr>
      <td class="tt">{{$timeList[$i]}}</td>

      @for ($j = 0; $j < 12; $j++)
      <td>
        {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
        <x-input.checkbox id="{{$j}}_{{$timeIdList[$i]}}" class="chk-wt2" name="chkWs" :icheck=false
          value="{{$j}}_{{$timeIdList[$i]}}" :editData=$editData />

        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$j}}_{{$timeIdList[$i]}}" v-on:click="timeClick"></div>
      </td>
      @endfor

      </tr>
    @endfor

  </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-group name="kaisuArea" />

    <x-bs.form-title>受講希望科目・回数</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small">
      <x-slot name="thead">
        <td>教科</td>
        <td>受講回数</td>
      </x-slot>
      <tr v-cloak>
        <x-bs.td-sp caption="教科">
          <x-input.select id="curriculumcd_1" :select2=true >
            <option value="1">国語</option>
            <option value="2">数学</option>
            <option value="3">理科</option>
            <option value="4">社会</option>
            <option value="5">英語</option>
          </x-input.select>
        </x-bs.td-sp>

        <x-bs.td-sp caption="回数">
          <x-input.text id="kaisu_1" />
        </x-bs.td-sp>
      </tr>

      <tr v-cloak>
        <x-bs.td-sp caption="教科">
          <x-input.select id="curriculumcd_2" :select2=true >
            <option value="1">国語</option>
            <option value="2">数学</option>
            <option value="3">理科</option>
            <option value="4">社会</option>
            <option value="5">英語</option>
          </x-input.select>
        </x-bs.td-sp>

        <x-bs.td-sp caption="受講回数">
          <x-input.text id="kaisu_2" />
        </x-bs.td-sp>
      </tr>
      <tr v-cloak>
        <x-bs.td-sp caption="教科">
          <x-input.select id="curriculumcd_3" :select2=true >
            <option value="1">国語</option>
            <option value="2">数学</option>
            <option value="3">理科</option>
            <option value="4">社会</option>
            <option value="5">英語</option>
          </x-input.select>
        </x-bs.td-sp>

        <x-bs.td-sp caption="受講回数">
          <x-input.text id="kaisu_3" />
        </x-bs.td-sp>
      </tr>
    </x-bs.table>

    <x-input.textarea caption="備考欄" id="memo" />

    <x-bs.callout type="warning">
      ※ご希望の日時ではなく、ご都合の悪い日程・時間を入力してください。<br>
      　ピンポイントでの日時のご指定をいただくと、授業の設定ができない場合がございます。
    </x-bs.callout>

  {{-- フッター --}}
  <x-slot name="footer">
    <div class="d-flex justify-content-end">
      <x-button.submit-edit caption="送信" />
    </div>
  </x-slot>

</x-bs.card>

@stop