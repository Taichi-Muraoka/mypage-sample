@extends('adminlte::page')

@section('title', '特別期間講習 科目別コマ組み')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('season_mng_student-detail', 1))

@section('parent_page_title', '生徒連絡内容')

@section('content')

{{-- フォームなし --}}
<x-bs.card class="pa-table-checked" :form=true>
    <x-slot name="card_title">
        CWテスト生徒１
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
      <tr>
          <th width="35%">特別期間名</th>
          <td>2023年春期</td>
      </tr>
      <tr>
          <th>校舎</th>
          <td>久我山</td>
      </tr>
      <tr>
        <th>教科</th>
        <td>数学</td>
      </tr>
      <tr>
        <th>授業数</th>
        <td>2</td>
      </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <p>下記表にて講習を入れたいコマに担当講師を割り当て、登録してください。</p>
  
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
          :disabled=false value="{{$j}}_{{$periodIdList[$i]}}" :editData=$editData />
        {{-- 表のDiv --}}
        <div class="chk-t" data-wt="{{$j}}_{{$periodIdList[$i]}}">
            @if (!in_array($j . "_" . $periodIdList[$i], $editData["chkWs"]))
            <div class="tt-button">
              <x-button.list-dtl caption="講師"/>
              @if ($i == 0 && $j== 0 )
              <span>CWテスト講師１０１</span>
              @endif
              @if ($i == 1 && $j== 2 )
              <span>CWテスト講師１０１</span>
              @endif
            </div>
            @endif
        </div>
      </td>
      @endfor

      </tr>
    @endfor

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.callout type="warning">
        当画面で登録された講習スケジュールは、科目固定・指導ブース自動割り当てとなります。<br>
        教室カレンダーから確認・編集を行うことができます。<br>
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('season_mng_student-detail', 1)}}" />
            <x-button.submit-edit caption="送信" />
          </div>
    </x-slot>

</x-bs.card>
{{-- モーダル --}}
@include('pages.admin.modal.season_mng_student-modal')

@stop