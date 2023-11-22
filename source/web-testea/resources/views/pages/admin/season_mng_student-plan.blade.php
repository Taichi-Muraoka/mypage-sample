@extends('adminlte::page')

@section('title', '特別期間講習 生徒教科別コマ組み')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('season_mng_student-detail', $seasonStudent->season_student_id))

@section('parent_page_title', '生徒日程詳細')

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>
    <x-slot name="card_title">
        {{$seasonStudent->student_name}}
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">特別期間名</th>
            <td>{{$seasonStudent->year}}年{{$seasonStudent->season_name}}</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$seasonStudent->campus_name}}</td>
        </tr>
        <tr>
            <th>教科</th>
            <td>{{$seasonStudent->subject_name}}</td>
        </tr>
        <tr>
            <th>教科別希望受講回数</th>
            <td>{{$seasonStudent->times}}</td>
        </tr>
    </x-bs.table>
    {{-- hidden 退避用--}}
    <x-input.hidden id="campus_cd" :editData=$seasonStudent />
    <x-input.hidden id="student_id" :editData=$seasonStudent />
    <x-input.hidden id="season_student_id" :editData=$seasonStudent />
    <x-input.hidden id="season_cd" :editData=$seasonStudent />
    <x-input.hidden id="subject_cd" :editData=$seasonStudent />

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <p>下記表にて講習を入れたいコマに担当講師を割り当て、登録してください。</p>

    {{-- 講師選択のバリデーションエラー時のメッセージ --}}
    <x-bs.form-group name="validate_selTutor" />
    {{-- スケジュール登録のバリデーションエラー時のメッセージ --}}
    <x-bs.form-group name="validate_schedule" />

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
          value="{{$date['dateId']}}_{{$periodKey}}" :editData=$editData  :exceptData=$exceptData/>

          {{-- 表のDiv --}}
          <div class="chk-t" data-wt="{{$date['dateId']}}_{{$periodKey}}">
            @if (!in_array($date['dateId'] . "_" . $periodKey, $editData['chkWs']))
                {{-- $editData['chkWs'] に指定されたセルは受講不可（グレー網掛け）のため、講師ボタン表示対象外 --}}
                @if (in_array($date['dateId'] . "_" . $periodKey, array_column($lessonInfo, 'key')))
                    {{-- $lessonInfo に設定されたセルは授業登録済み（緑網掛け）のため、授業情報表示 --}}
                    @for ($i = 0; $i < count($lessonInfo); $i++)
                        @if ($lessonInfo[$i]['key'] == $date['dateId'] . "_" . $periodKey)
                        <div class="class-info">
                            <span>{{$lessonInfo[$i]['tutor']}}<br>
                                {{$lessonInfo[$i]['subject']}}</span>
                        </div>
                        @endif
                    @endfor
                @else
                    {{-- 上記以外のセルはコマ組み可のセルのため、講師ボタンを表示 --}}
                    <div class="sel-button">

                    {{-- dataAttrに日付_時限のIDをセットする --}}
                    <x-button.list-dtl caption="講師" :dataAttr="['date_period_key' => $date['dateId'] . '_' . $periodKey]"/>

                    {{-- 講師名表示用 波括弧の入れ子ができず文字コードで設定 --}}
                    <span v-cloak>&#123;&#123; form.sel_tname_{{$date['dateId']}}_{{$periodKey}} &#125;&#125;</span>
                    <x-input.hidden :id="'sel_tname_' . $date['dateId'] . '_' . $periodKey" :editData=$editData />
                    <x-input.hidden :id="'sel_tid_' .$date['dateId'] . '_' . $periodKey" :editData=$editData />

                    </div>
                @endif
            @endif
          </div>
        </td>
        @endforeach
    </tr>
    @endforeach

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.callout type="warning">
        当画面で登録された講習スケジュールは、教科固定・両者通塾・ブース自動割り当てとなります。<br>
        教室カレンダーから確認・編集を行うことができます。<br>
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 二階層目に戻る --}}
            <x-button.back url="{{route('season_mng_student-detail', $seasonStudent->season_student_id)}}" />
            <x-button.submit-new caption="送信" />
          </div>
    </x-slot>

</x-bs.card>

{{-- フォームモーダル --}}
@include('pages.admin.modal.season_mng_student-modal', ['modal_form' => true])

@stop
