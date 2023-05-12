@extends('adminlte::page')

@section('title', '特別期間講習　日程連絡')

@section('content')

{{-- フォーム --}}
<x-bs.card class="pa-table-checked" :form=true >

  <p>下記の特別期間について、<b>授業に入れない日程・時間</b>を選択・登録してください。</p>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
      <tr>
          <th class="t-minimum" width="25%">特別期間</th>
          <td>2023年春期</td>
      </tr>
      <tr>
        <th>提出締め切り</th>
        <td>3/10</td>
    </tr>
</x-bs.table>

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

  {{-- フッター --}}
  <x-slot name="footer">
    <div class="d-flex justify-content-end">
      <x-button.submit-edit caption="送信" />
    </div>
  </x-slot>

</x-bs.card>

@stop