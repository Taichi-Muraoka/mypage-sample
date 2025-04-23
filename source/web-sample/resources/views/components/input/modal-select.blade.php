{{------------------------------------------
    input - モーダル選択
  --------------------------------------------}}

@props(['caption' => '', 'id' => '', 'editData' => [], 'vShow' => '',
  'btnCaption' => '','dataTarget' => '', 'href' => '', 'vueDataAttr' => [], 'dataAttr' => [], 
  'disabled' => false, 'class' => '', 'vueDisabled' => '', 'vueHref' => ''])

{{-- バリデーションエラー時のスクロール先 --}}
<span class="form-validation" data-id="{{ $id }}"></span>

<div class="form-group"
  {{-- v-show --}}
  @if ($vShow)
  v-show="{{ $vShow }}"
  @endif >

  {{-- ラベル --}}
  @if (!empty($caption))
  <label for="{{ $id }}"><span class="input-title">{{ $caption }}</span></label>
  @endif

  <div class="input-group">

    <div class="custom-file">
      <input type="text" class="form-control bg-white" id="text_{{ $id }}" 
      {{-- placeholderはデフォルトでcaptionにした --}}
      placeholder="@if (empty($placeholder)){{ $caption }}@else{{ $placeholder }}@endif"
      {{-- vue --}}
      v-model="form.text_{{ $id }}"
        {{-- エラー時の表示 --}}
        v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }"
      {{-- 編集時にデータをセット --}}
      @isset($editData[$id])
      value="{{$editData['text_'.$id]}}"
      @endisset
      {{-- readolny --}}
      readonly>
    </div>

    <div class="input-group-append">
      {{-- モーダル表示ボタン --}}
      <button type="button" class="btn input-group-text"
      v-bind:class="{ 'error-btn': form_err.class.{{ $id }} || form_err.class.file_{{ $id }} }" 
      data-toggle="modal"

      {{-- 選択モーダルのid --}}
      data-modalSelectId="{{ $id }}"

      {{-- 開くモーダルを指定。動的に指定する場合は、vueDataAttr=['target' => 'xxx'] のように指定するのでそれ以外の場合 --}} 
      @if (!isset($vueDataAttr['target']))
      data-target="@if (empty($dataTarget)){{ '#modal-dtl' }}@else{{ $dataTarget }}@endif" 
      @endif
    
      {{-- buttonに対するdata属性の定義。vueで取得する用。 --}}
      @foreach($vueDataAttr as $key => $val)
       :data-{{$key}}="{{ $vueDataAttr[$key] }}"
      @endforeach
      >
      @if (empty($btnCaption)){{ '選択' }}@else{{ $btnCaption }}@endif
        </button>

      {{-- 取り消しボタン --}}
      <button type="button" class="btn input-group-text"
      {{-- 選択モーダルのid --}}
      data-modalSelectId="{{ $id }}"
      v-on:click="modalSelectClear"
      v-bind:class="{ 'error-btn': form_err.class.{{ $id }} || form_err.class.file_{{ $id }} }" 
      >取消</button>
    </div>

  </div>

  <div class="mt-1">
    {{-- 編集時にデータをセット --}} 
    <input type="hidden" id="{{ $id }}" v-model="form.{{ $id }}" 
    @isset($editData[$id])
    value="{{$editData[$id]}}"
    @endisset
    >
  </div>

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
    <li v-for="msg in form_err.msg.file_{{ $id }}">
      @{{ msg }}
    </li>
  </ul>
</div>