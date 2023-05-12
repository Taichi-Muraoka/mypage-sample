@extends('adminlte::page')

@section('title', '特別期間講習 コマ組み')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォームなし --}}
<x-bs.card class="pa-table-checked">
    <x-slot name="card_title">
        CWテスト生徒１
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
      <tr>
          <th width="35%">校舎</th>
          <td>久我山</td>
      </tr>
      <tr>
          <th>生徒コメント</th>
          <td>今回は数学・英語の受講を希望します</td>
      </tr>
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
    <div class="mb-5"></div>

    <x-bs.form-title>下記の表より割り当て可能な講師を確認し、講習スケジュールを登録してください。</x-bs.form-title>

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
      @php
      $disableclass = false;
      if($j == 7) {
        $disableclass = true;
      }
      @endphp
      <td>
        {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
        <x-input.checkbox id="{{$j}}_{{$timeIdList[$i]}}" class="chk-wt2" name="chkWs" :icheck=false
          :disabled=false value="{{$j}}_{{$timeIdList[$i]}}" :editData=$editData />
        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$j}}_{{$timeIdList[$i]}}" v-on:click="timeClick">
            @if (!in_array($j . "_" . $timeIdList[$i], $editData["chkWs"]))
            <div class="tt-button">
            <x-button.list-dtl caption="講師"/>
            </div>
            @endif
        </div>
      </td>
      @endfor

      </tr>
    @endfor

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-bs.form-group name="kaisuArea" />
    <x-bs.form-title>講習スケジュール登録</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small">
        <x-slot name="thead">
          <td>受講日</td>
          <td>時限</td>
          <td>担当講師</td>
          <td>教科</td>
        </x-slot>
        @for ($i = 1; $i <= 4; $i++)
        <tr>
          <x-bs.td-sp caption="受講日">
            <x-input.select id="lesson_date_{{$i}}" :select2=true >
              <option value="1">3/27</option>
              <option value="2">3/28</option>
              <option value="3">3/29</option>
              <option value="4">3/30</option>
              <option value="5">3/31</option>
              <option value="6">4/1</option>
              <option value="7">4/3</option>
              <option value="8">4/4</option>
              <option value="9">4/5</option>
              <option value="10">4/6</option>
              <option value="11">4/7</option>
              <option value="12">4/8</option>
            </x-input.select>
          </x-bs.td-sp>

          <x-bs.td-sp caption="時限">
            <x-input.select id="period_{{$i}}" :select2=true >
              <option value="1">1限</option>
              <option value="2">2限</option>
              <option value="3">3限</option>
              <option value="4">4限</option>
              <option value="5">5限</option>
              <option value="6">6限</option>
              <option value="7">7限</option>
            </x-input.select>
          </x-bs.td-sp>

          <x-bs.td-sp caption="担当講師">
            <x-input.select id="tid_{{$i}}" :select2=true >
              <option value="1">CWテスト講師１０１</option>
              <option value="2">CWテスト講師１０２</option>
              <option value="3">CWテスト講師１０３</option>
              <option value="4">CWテスト講師１０４</option>
              <option value="5">CWテスト講師１０５</option>
            </x-input.select>
          </x-bs.td-sp>

          <x-bs.td-sp caption="教科">
            <x-input.select id="curriculumcd_{{$i}}" :select2=true >
              <option value="1">国語</option>
              <option value="2">数学</option>
              <option value="3">理科</option>
              <option value="4">社会</option>
              <option value="5">英語</option>
            </x-input.select>
          </x-bs.td-sp>

        </tr>
        @endfor
      </x-bs.table>

      <x-input.select id="status" caption="コマ組みステータス" :select2=true :select2Search=false>
        <option value="1" selected>未対応</option>
        <option value="2">対応中</option>
        <option value="3">対応済</option>
      </x-input.select>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.callout type="warning">
        当画面で登録された講習スケジュールは、仮確定の状態です。<br>
        教室カレンダーから確認・編集を行うことができます。<br>
        スケジュールを確定し、生徒・講師へ公開するには、対象生徒全員のスケジュールを登録後に確定処理を行ってください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>
{{-- モーダル --}}
@include('pages.admin.modal.season_mng_student-modal')

@stop