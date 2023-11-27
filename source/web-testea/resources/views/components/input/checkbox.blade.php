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
  editData: 編集データ
  exceptData: チェック対象外データ
  class: クラス
  icheck: icheckを使うかどうか
--}}
@props(['caption' => '', 'id' => '', 'name' => '', 'value' => '', 'checked' => false,
'disabled' => false, 'editData' => [],'vShow' => '', 'exceptData' => [],'class' => '', 'icheck' => true])

{{-- v-show --}}
@if ($vShow)
<div v-show="{{ $vShow }}">
@endif

@if ($icheck)
<div class="icheck-primary d-inline mr-3">
@endif

  <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
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
  <label for="{{ $id }}">
    {{ $caption }}
  </label>
  @endif

@if ($icheck)
</div>
@endif
{{-- v-show --}}
@if ($vShow)
</div>
@endif
