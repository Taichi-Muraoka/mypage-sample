@extends('adminlte::page')

@section('title', '空き時間登録')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

  <p>現在入っているレギュラー授業以外で、授業実施可能なコマ（空き時間）を登録してください。</p>

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
          value="{{$weekdayKey}}_{{$timeIdList[$i]}}" :editData=$editData :exceptData=$exceptData />

        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$weekdayKey}}_{{$timeIdList[$i]}}" v-on:click="timeClick"></div>
      </td>
      @endforeach

      </tr>
    @endfor

  </x-bs.table>

  <p><br>黒色：レギュラー授業　　緑色：空き時間</p>

  {{-- フッター --}}
  <x-slot name="footer">
    <div class="d-flex justify-content-end">
      <x-button.submit-edit caption="送信" />
    </div>
  </x-slot>

</x-bs.card>

@stop