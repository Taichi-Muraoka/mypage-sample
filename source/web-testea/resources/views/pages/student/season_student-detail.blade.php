@extends('adminlte::page')

@section('title', '特別期間講習 連絡内容詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォームなし --}}
<x-bs.card>

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
      <tr>
          <th>生徒コメント</th>
        {{-- nl2br: 改行 --}}
          <td class="nl2br">{{$seasonStudent->comment}}</td>
      </tr>
    </x-bs.table>
    {{-- hidden 退避用--}}
    <x-input.hidden id="campus_cd" :editData=$seasonStudent />
    <x-input.hidden id="season_student_id" :editData=$seasonStudent />
    <x-input.hidden id="season_cd" :editData=$seasonStudent />

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>受講希望教科・受講回数</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table class="mb-3">
        <x-slot name="thead">
            <th width="30%">教科</th>
            <th>受講回数</th>
        </x-slot>

        {{-- テーブル行 --}}
        @foreach ($subjectTimesList as $subjectTimes) <tr>
          <x-bs.td-sp caption="教科">{{$subjectTimes->subject_name}}</x-bs.td-sp>
          <x-bs.td-sp caption="受講回数">{{$subjectTimes->times}}</x-bs.td-sp>
        </tr>
        @endforeach
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

  <x-bs.form-title>受講</x-bs.form-title>
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
        <div class="chk-t" data-wt="{{$date['dateId']}}_{{$periodKey}}"></div>
      </td>
      @endforeach

      </tr>
    @endforeach

  </x-bs.table>

  {{-- フッター --}}
  <x-slot name="footer">
        <div class="d-flex justify-content-start">
            <x-button.back />
        </div>
  </x-slot>

</x-bs.card>

@stop