@extends('adminlte::page')

@section('title', '特別期間講習 生徒日程詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォームなし --}}
<x-bs.card :form=true>
    <x-slot name="card_title">
        CWテスト生徒１
    </x-slot>

    <p>生徒の連絡内容を確認し、科目毎にコマ組みを行います。<br>
    登録した講習スケジュールを確認し、コマ組みステータスを登録してください。</p>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
      <tr>
          <th width="35%">特別期間名</th>
          <td>2023年春期</td>
      </tr>
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

  <x-bs.form-title>受講希望科目・授業数</x-bs.form-title>

  {{-- テーブル --}}
    <x-bs.table class="mb-3">
        <x-slot name="thead">
            <th width="30%">受講希望科目</th>
            <th>授業数</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>数学</td>
            <td>2</td>
            <td>
              <x-button.list-edit href="{{ route('season_mng_student-detail', '') }}/1/plan/1" caption="コマ組み" />
            </td>
      </tr>
        <tr>
            <td>英語</td>
            <td>2</td>
            <td>
              <x-button.list-edit href="{{ route('season_mng_student-detail', '') }}/1/plan/1" caption="コマ組み" />
            </td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>登録済みの講習スケジュール一覧</x-bs.form-title>

  {{-- テーブル --}}
  <x-bs.table class="mb-3">
    <x-slot name="thead">
        <th>受講日</th>
        <th>時限</th>
        <th>担当講師</th>
        <th>科目</th>
    </x-slot>

    {{-- テーブル行 --}}
    <tr>
        <td>2023/03/27</td>
        <td>1</td>
        <td>CWテスト講師１０１</td>
        <td>数学</td>
    </tr>
    <tr>
      <td>2023/03/29</td>
      <td>2</td>
      <td>CWテスト講師１０１</td>
      <td>数学</td>
    </tr>
    <tr>
      <td>2023/04/01</td>
      <td>4</td>
      <td>CWテスト講師１０２</td>
      <td>英語</td>
    </tr>
    <tr>
      <td>2023/04/07</td>
      <td>4</td>
      <td>CWテスト講師１０２</td>
      <td>英語</td>
    </tr>
  </x-bs.table>

      <x-input.select id="status" caption="コマ組みステータス" :select2=true :select2Search=false>
        <option value="1" selected>未対応</option>
        <option value="2">対応中</option>
        <option value="3">対応済</option>
      </x-input.select>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.callout type="warning">
        全ての受講科目についてコマ組みを行った後、コマ組みステータスを「対応済」としてください。<br>
        当機能でコマ組みされた講習スケジュールは仮確定の状態であり、生徒・講師への公開はされません。<br>
        教室カレンダーから確認・編集を行うことができます。<br>
        スケジュールを確定し、生徒・講師へ公開するには、対象生徒全員のスケジュールを登録後に確定処理を行ってください。
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