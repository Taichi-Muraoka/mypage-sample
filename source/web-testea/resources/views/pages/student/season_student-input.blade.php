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
          <td>{{$seasonStudent->year}}年{{$seasonStudent->season_name}}</td>
      </tr>
      <tr>
        <th>受講校舎</th>
        <td>{{$seasonStudent->campus_name}}</td>
      </tr>
    </x-bs.table>
    {{-- hidden 退避用--}}
    <x-input.hidden id="campus_cd" :editData=$seasonStudent />
    <x-input.hidden id="season_student_id" :editData=$seasonStudent />
    <x-input.hidden id="season_cd" :editData=$seasonStudent />

  {{-- 登録期間外のエラー時のメッセージ --}}
  <x-bs.form-group name="s_date_term" />
  {{-- チェックボックスのエラー時のメッセージ --}}
  <x-bs.form-group name="chkWs" />

  <x-bs.table :hover=false class="table-checked">

    {{-- テーブルタイトル行 --}}
    <x-slot name="thead">
      <th class="t-minimum t-week-time"></th>

      {{-- 時限を表示 --}}
      @foreach ($periodList as $periodKey => $periodVal)
        <th class="t-week">{{$periodKey}}時限目</th>
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

    <x-bs.form-group name="kaisuArea" />

    <x-bs.form-title>受講希望教科・受講回数</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :bordered=false :hover=false>
      <x-slot name="thead">
        <td>教科</td>
        <td>受講回数</td>
      </x-slot>
      @for ($i = 1; $i <= 5; $i++)
      <tr v-cloak>
        <x-bs.td-sp caption="教科">
          <x-input.select id="subject_cd_{{ $i }}" :select2=true :mastrData=$subjects :editData="$editData"
            :select2Search=true :blank=true :rules=$rules />
        </x-bs.td-sp>

        <x-bs.td-sp caption="受講回数">
          <x-input.text id="times_{{ $i }}" :rules=$rules />
        </x-bs.td-sp>
      </tr>
      @endfor

    </x-bs.table>

    <x-input.textarea caption="備考欄" id="comment" :rules=$rules />

    <x-bs.callout type="warning">
      ※ご希望の日時ではなく、ご都合の悪い日程・時限を入力してください。<br>
      &emsp;ピンポイントでの日時のご指定をいただくと、授業の設定ができない場合がございます。<br>
      ※早めのご登録をお願いいたします。登録いただいた方から順次スケジュールを作成いたします。
    </x-bs.callout>

  {{-- フッター --}}
  <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

      <x-button.submit-edit caption="送信" />
    </div>
  </x-slot>

</x-bs.card>

@stop