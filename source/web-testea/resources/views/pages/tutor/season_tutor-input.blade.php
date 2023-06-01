@extends('adminlte::page')

@section('title', '特別期間講習　日程連絡')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true >

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

      {{-- 時限を表示 --}}
      @for ($i = 0; $i < count($periodList); $i++)
        <th class="t-week">{{$periodList[$i]}}</th>
      @endfor
    </x-slot>

    {{-- 二重ループで組み立てる --}}
    @for ($j = 0; $j < count($dayList); $j++) <tr>
      {{-- 日付を表示 --}}
      <td class="tt">{{$dayList[$j]}}</td>

      @for ($i = 0; $i < count($periodList); $i++)
      <td>
        {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
        <x-input.checkbox id="{{$j}}_{{$periodIdList[$i]}}" class="chk-wt2" name="chkWs" :icheck=false
          value="{{$j}}_{{$periodIdList[$i]}}" :editData=$editData />

        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$j}}_{{$periodIdList[$i]}}" v-on:click="timeClick"></div>
      </td>
      @endfor

      </tr>
    @endfor

  </x-bs.table>

  {{-- フッター --}}
  <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

      <x-button.submit-new />
    </div>
  </x-slot>

</x-bs.card>

@stop