@extends('adminlte::page')

@section('title', '特別期間講習 講師日程詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォームなし --}}
<x-bs.card>
    <x-slot name="card_title">
        {{$tutor_name}}
    </x-slot>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true>
      <tr>
          <th width="35%">特別期間名</th>
          <td>{{$seasonTutor->year}}年{{$seasonTutor->season_name}}</td>
      </tr>
      <tr>
          <th>講師コメント</th>
          {{-- nl2br: 改行 --}}
          <td class="nl2br">{{$seasonTutor->comment}}</td>
      </tr>
  </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>授業不可コマ連絡情報</x-bs.form-title>
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
        <x-input.checkbox id="{{$date['dateId']}}_{{$periodKey}}" class="chk-season-plan" name="chkWs" :icheck=false
          value="{{$date['dateId']}}_{{$periodKey}}" :editData=$editData  :exceptData=$exceptData/>

          {{-- 表のDiv --}}
          <div class="chk-t" data-wt="{{$date['dateId']}}_{{$periodKey}}">
            @if (!in_array($date['dateId'] . "_" . $periodKey, $exceptData))
                {{-- $exceptData に指定されたセルは受講不可（グレー網掛け）のため、授業情報表示対象外 --}}
                @for ($i = 0; $i < count($lessonInfo); $i++)
                    @if ($lessonInfo[$i]['key'] == $date['dateId'] . "_" . $periodKey)
                    <div class="class-info">
                        <span>{{$lessonInfo[$i]['student']}}</span>
                    </div>
                    @endif
                @endfor
            @endif
          </div>
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
