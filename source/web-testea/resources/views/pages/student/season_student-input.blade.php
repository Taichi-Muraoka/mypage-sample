@extends('adminlte::page')

@section('title', '特別期間講習 日程連絡')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true >

  <p>下記の特別期間について、<b>ご都合の悪い日程・時限</b>を選択し、受講希望科目・回数を入力してください。</p>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
      <tr>
          <th width="25%">特別期間</th>
          <td>2023年春期</td>
      </tr>
      <tr>
        <th>受講校舎</th>
        <td>久我山</td>
      </tr>
      <tr>
          <th>受講回数（目安）</th>
          <td>4回</td>
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

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-group name="kaisuArea" />

    <x-bs.form-title>受講希望科目・受講回数</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false :smartPhone=true class="mb-small">
      <x-slot name="thead">
        <td>科目</td>
        <td>受講回数</td>
      </x-slot>
      @for ($i = 1; $i <= 5; $i++)
      <tr v-cloak>
        <x-bs.td-sp caption="科目">
          <x-input.select id="curriculumcd_{{ $i }}" :select2=true >
            <option value="1">国語</option>
            <option value="2">数学</option>
            <option value="3">理科</option>
            <option value="4">社会</option>
            <option value="5">英語</option>
          </x-input.select>
        </x-bs.td-sp>

        <x-bs.td-sp caption="回数">
          <x-input.text id="kaisu_{{ $i }}" />
        </x-bs.td-sp>
      </tr>
      @endfor

    </x-bs.table>

    <x-input.textarea caption="備考欄" id="memo" />

    <x-bs.callout type="warning">
      ※ご希望の日時ではなく、ご都合の悪い日程・時限を入力してください。<br>
      　ピンポイントでの日時のご指定をいただくと、授業の設定ができない場合がございます。<br>
      ※早めのご登録をお願いいたします。登録いただいた方から順次スケジュールを作成いたします。
    </x-bs.callout>

  {{-- フッター --}}
  <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

      <x-button.submit-new />
    </div>
  </x-slot>

</x-bs.card>

@stop