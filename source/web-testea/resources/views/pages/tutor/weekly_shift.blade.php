@extends('adminlte::page')

@section('title', '空き時間登録')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

  <p>空き時間を選択してください。</p>

  {{-- チェックボックスのエラー時のメッセージ --}}
  <x-bs.form-group name="chkWs" />

  <x-bs.table :hover=false class="table-checked">

    {{-- テーブルタイトル行 --}}
    <x-slot name="thead">
      <th class="t-minimum t-week-time"></th>

      {{-- 曜日を表示 --}}
      @foreach ($weekdayList as $key => $obj)
      <th class="t-week">{{$obj->value}}</th>
      @endforeach
    </x-slot>

    {{-- 二重ループで組み立てる --}}
    @for ($i = 0; $i < count($timeList); $i++) <tr>
      <td class="tt">{{$timeList[$i]}}</td>

      @foreach ($weekdayList as $weekdayKey => $weekdayVal)
      <td>
        {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
        <x-input.checkbox id="{{$weekdayKey}}_{{$timeIdList[$i]}}" class="chk-wt" name="chkWs" :icheck=false
          value="{{$weekdayKey}}_{{$timeIdList[$i]}}" :editData=$editData />

        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$weekdayKey}}_{{$timeIdList[$i]}}" v-on:click="timeClick"></div>
      </td>
      @endforeach

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