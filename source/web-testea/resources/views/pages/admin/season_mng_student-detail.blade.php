@extends('adminlte::page')

@section('title', '特別期間講習　生徒提出スケジュール詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォームなし --}}
<x-bs.card>
    <x-slot name="card_title">
        CWテスト生徒１
    </x-slot>
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

    <x-bs.form-title>受講希望科目・回数</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table class="mb-3">
        <x-slot name="thead">
            <th width="30%">教科</th>
            <th>授業時間数</th>
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

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>生徒コメント欄</x-bs.form-title>

    <x-bs.table :hover=false class="mb-3">
        {{-- テーブル行 --}}
        <tr>
            <td>今回は数学・英語の受講を希望します</td>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-start">
            <x-button.back />
        </div>
    </x-slot>

</x-bs.card>

@stop