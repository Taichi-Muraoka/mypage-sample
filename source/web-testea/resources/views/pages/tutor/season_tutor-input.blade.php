@extends('adminlte::page')

@section('title', '特別期間講習 日程連絡')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true >

  <p>下記の特別期間について、<b>授業に入れない日程・時限</b>を選択・登録してください。</p>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
      <tr>
        <th width="40%">特別期間</th>
        <td>{{$seasonName->year}}年{{$seasonName->season_name}}</td>
      </tr>
    </x-bs.table>
    <x-input.hidden id="season_cd" :editData=$editData />

  {{-- 登録期間外のエラー時のメッセージ --}}
  <x-bs.form-group name="t_date_term" />
  {{-- チェックボックスのエラー時のメッセージ --}}
  <x-bs.form-group name="chkWs" />

  <x-bs.table :hover=false class="table-checked">

    {{-- テーブルタイトル行 --}}
    <x-slot name="thead">
      <th class="t-minimum t-period-day"></th>

      {{-- 時限を表示 --}}
      @foreach ($periodList as $periodKey => $periodVal)
        <th class="t-period">{{$periodKey}}限</th>
      @endforeach
    </x-slot>

    {{-- 二重ループで組み立てる --}}
    @foreach ($dateList as $date) <tr>
      {{-- 日付を表示 --}}
      <td class="tt">{{$date['dateLabel']}}</td>

      @foreach ($periodList as $periodKey => $periodVal)
      <td>
        {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
        <x-input.checkbox id="{{$date['dateId']}}_{{$periodKey}}" class="chk-wt2" name="chkWs" :icheck=false
          value="{{$date['dateId']}}_{{$periodKey}}" :editData=$editData />

        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$date['dateId']}}_{{$periodKey}}" v-on:click="timeClick"></div>
      </td>
      @endforeach

      </tr>
    @endforeach

  </x-bs.table>
  
  {{-- 余白 --}}
  <div class="mb-3"></div>
  <x-input.textarea caption="備考欄" id="comment" :rules=$rules />

  {{-- フッター --}}
  <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

      <x-button.submit-new />
    </div>
  </x-slot>

</x-bs.card>

@stop