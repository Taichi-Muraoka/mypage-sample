{{------------------------------------------
    input - file
  --------------------------------------------}}

{{--
  caption: 表示
  id: ID
  name: チェックボックス名（他と違い、nameがFormのプロパティ名になる）
  value: 値
  checked: デフォルトのチェック状態。値がない場合などデフォルトで選択される
  disabled: disabled（選択不可）にするかどうか
  vShow: Vue.jsのv-show
  vBind: Vue.jsのv-bind
  editData: 編集データ
  exceptData: チェック対象外データ
  class: クラス
  icheck: icheckを使うかどうか
--}}
@props(['caption' => '', 'id' => '', 'name' => '', 'value' => '', 'checked' => false,
'disabled' => false, 'editData' => [],'vShow' => '', 'vBind' => false, 'exceptData' => [],'class' => '', 'icheck' => true])

{{-- v-show --}}
@if ($vShow)
<div v-show="{{ $vShow }}">
@endif

@if ($icheck)
<div class="icheck-primary d-inline mr-3">
@endif

  <input type="checkbox" name="{{ $name }}"
  @if ($vBind)
  {{-- v-bind（idとvalueをvueで動的に設定する場合） --}}
  v-bind:id="{{ $id }}"  v-bind:value="{{ $value }}"
  @else
  {{-- 通常の場合 --}}
  id="{{ $id }}" value="{{ $value }}"
  @endif
  v-model="form.{{ $name }}"

  {{-- クラス --}}
  class="@if (!empty($class)){{ $class }}@endif"

  {{-- disabled --}}
  @if ($disabled)
  disabled
  @endif

  {{-- チェック対象外データのdisabled --}}
  @if (isset($exceptData) && in_array($value, $exceptData))
  disabled
  @endif

  {{-- チェック状態 --}}
  @if ($checked && (!isset($editData) || !isset($editData[$name])))
  {{-- デフォルト。editDataがない場合 --}}
  checked
  @elseif (isset($editData[$name]) && in_array($value, $editData[$name]))
  {{-- 値がある場合。配列想定とした。in_arrayでチェックする --}}
  checked
  @endif
  >

  @if (!empty($caption))
    @if ($vBind)
    {{-- v-bind（id・value・captionをvueで動的に設定する場合） --}}
     <label v-bind:for="{{ $id }}" v-text="{{ $caption }}"></label>
    @else
    {{-- 通常の場合 --}}
    <label for="{{ $id }}">
      {{ $caption }}
    </label>
    @endif
  @endif

@if ($icheck)
</div>
@endif
{{-- v-show --}}
@if ($vShow)
</div>
@endif
