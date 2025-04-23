{{------------------------------------------
    input - select
  --------------------------------------------}}

{{--
  caption: 項目名
  id: ID
  select2: 使用するかどうか
  select2Search: select2の検索を使うかどうか
  mastrData: プルダウンの選択項目 keyとvalue.valueとなっていること
  editData: 編集データ
  onChange: Vueのチェンジイベント
  blank: 選択してくださいを表示するかどうか
  vShow: Vue.jsのv-show
  blankText: 選択してくださいの代わりのテキスト
  emptyValue: 選択してくださいの値
  multiple: 複数選択指定
--}}
@props(['caption' => '', 'id' => '', 'select2' => false, 'select2Search' => true,
    'mastrData' => [], 'editData' => [], 'onChange' => '', 'blank' => true, 'vShow' => '',
    'blankText' => '', 'emptyValue' => '', 'multiple' => ''])

{{-- バリデーションエラー時のスクロール先 --}}
<span class="form-validation" data-id="{{ $id }}"></span>

<div class="form-group"
  {{-- v-show --}}
  @if ($vShow)
  v-show="{{ $vShow }}"
  @endif>

  {{-- ラベル --}}
  @if (!empty($caption))
  <label for="{{ $id }}"><span class="input-title">{{ $caption }}</span></label>
  @endif

  {{-- select2はこっちで赤くする --}}
  @if ($select2)
  <div v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }">
  @endif

  {{-- select2の場合width100じゃないとブラウザサイズを変更した際にリサイズしない --}}
  <select class="form-control @if ($select2) select2 @endif" id="{{ $id }}" @if ($select2) style="width: 100%;" @endif
    v-model="form.{{ $id }}"

    {{-- Vueのチェンジイベント --}}
    @if (!empty($onChange))
    v-on:change="{{ $onChange }}"
    @endif

    @if (!$select2)
    {{-- select2以外のエラー --}}
    v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }"
    @else
    {{-- select2のチェンジイベント。vue対応 --}}
    v-select="form.{{ $id }}"
    @endif

    {{-- 検索を無効にする場合 --}}
    @if ($select2 && !$select2Search)
    data-minimum-results-for-search="Infinity"
    @endif

    {{-- 複数選択指定の場合 --}}
    @if ($multiple)
    multiple
    @endif

    >

    {{-- デフォルトの項目 --}}
    @if ($blank)
    <option value="{{$emptyValue}}">@if ($blankText) {{$blankText}} @else 選択して下さい @endif</option>
    @endif

    {{-- マスターの表示 --}}
    @foreach ($mastrData as $key => $obj)
    <option value="{{$key}}"
      {{-- 選択 --}}
      {{-- 複数選択指定の場合 --}}
      @if (($multiple) && isset($editData[$id]))
        @foreach(explode("," , $editData[$id]) as $val)
          @if (!empty($val) && $val == $key)
          selected
          @endif
        @endforeach
      {{-- 単一選択の場合 --}}
      @elseif (isset($editData[$id]) && $editData[$id] == $key)
        selected
      @endif
      {{-- 表示名は、オブジェクトでも配列でも良いとした --}}
    >@if (is_object($obj)) {{$obj->value}} @else {{$obj['value']}} @endif</option>
    @endforeach

    {{-- モック用 --}}
    {{ $slot }}
  </select>

  @if ($select2)
  </div>
  @endif

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
  </ul>

</div>
