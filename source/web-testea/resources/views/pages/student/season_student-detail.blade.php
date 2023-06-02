@extends('adminlte::page')

@section('title', '特別期間講習　連絡内容詳細')

@section('content')

{{-- フォームなし --}}
<x-bs.card>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
      <tr>
          <th class="t-minimum" width="25%">特別期間</th>
          <td>2023年春期</td>
      </tr>
        <tr>
          <th>受講校舎</th>
          <td>久我山</td>
      </tr>
      <tr>
          <th>生徒コメント</th>
          <td>今回は数学・英語の受講を希望します</td>
      </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

  <x-bs.form-title>受講希望科目・授業数</x-bs.form-title>

  {{-- テーブル --}}
    <x-bs.table class="mb-3">
        <x-slot name="thead">
            <th width="30%">受講希望科目</th>
            <th>受講回数</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>数学</td>
            <td>2</td>
      </tr>
        <tr>
            <td>英語</td>
            <td>2</td>
        </tr>
    </x-bs.table>

  <x-bs.table :hover=false class="table-checked">

    {{-- テーブルタイトル行 --}}
    <x-slot name="thead">
      <th class="t-minimum t-week-time"></th>

      {{-- 時限を表示 --}}
      @for ($i = 0; $i < count($periodIdList); $i++)
        <th class="t-week">{{$periodIdList[$i]}}時限目</th>
      @endfor
    </x-slot>

    {{-- 二重ループで組み立てる --}}
    @for ($j = 0; $j < count($dayList); $j++) <tr>
      {{-- 日付を表示 --}}
      <td class="tt">{{$dayList[$j]}}</td>

      @for ($i = 0; $i < count($periodIdList); $i++)
      <td>
        {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
        <x-input.checkbox id="{{$dayIdList[$j]}}_{{$periodIdList[$i]}}" class="chk-wt2" name="chkWs" :icheck=false
          value="{{$dayIdList[$j]}}_{{$periodIdList[$i]}}" :editData=$editData />

        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$dayIdList[$j]}}_{{$periodIdList[$i]}}"></div>
      </td>
      @endfor

      </tr>
    @endfor

  </x-bs.table>

  {{-- フッター --}}
  <x-slot name="footer">
        <div class="d-flex justify-content-start">
            <x-button.back />
        </div>
  </x-slot>

</x-bs.card>

@stop