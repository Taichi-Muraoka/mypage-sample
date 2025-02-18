{{------------------------------------------ 
    input - file
  --------------------------------------------}}

{{--
  caption: 表示
  id: ID
  name: ラジオ名（他と違い、nameがFormのプロパティ名になる）
  value: 値
  checked: デフォルトのチェック状態。値がない場合などデフォルトで選択される
  editData: 編集データ
--}}
@props(['caption' => '', 'id' => '', 'name' => '', 'value' => '', 'checked' => false, 
  'editData' => []])

{{-- バリデーションエラーはform-group.blade.phpを使用する --}}
 
<div class="icheck-primary d-inline mr-3">
  <input type="radio" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" 
  v-model="form.{{ $name }}" 

  {{-- チェック状態 --}}
  @if ($checked && (!isset($editData) || !isset($editData[$name])))
  {{-- デフォルト。editDataがない場合 --}}
  checked
  @elseif (isset($editData[$name]) && $editData[$name] == $value)
  {{-- 値がある場合 --}}
  checked
  @endif

  >
  <label for="{{ $id }}">
    {{ $caption }}
  </label>

</div>