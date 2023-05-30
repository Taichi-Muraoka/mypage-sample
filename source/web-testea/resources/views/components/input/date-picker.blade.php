{{------------------------------------------ 
    input - datepicker
  --------------------------------------------}}

{{-- 
  editData: 編集用のデータ
  vShow: Vue.jsのv-show
--}}

@props(['caption' => '', 'id' => '', 'editData' => [], 'vShow' => ''])

{{-- バリデーションエラー時のスクロール先 --}}
<span class="form-validation" data-id="{{ $id }}"></span>

<div class="form-group"
  {{-- v-show --}}
  @if ($vShow)
  v-show="{{ $vShow }}"
  @endif>

  {{-- ラベル --}}
  @if (!empty($caption))
  <label for="_{{ $id }}"><span class="input-title">{{ $caption }}</span></label>
  @endif
  
  <div class="input-group">
    <div class="input-group-prepend">
      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
  </div>

  {{-- バリデート結果のエラー時に色を変える。v-bind --}}
  <input type="text" 
    class="form-control date-picker" 
    id="_{{ $id }}" {{-- vue管理はしない v-model="form._{{ $id }}" --}} 
    v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }" 
    {{-- 編集時にデータをセット --}} 
    {{-- MySQLのフォーマットで大丈夫 value="2019-08-02" --}}
    @isset($editData[$id])
    {{-- Carbonならフォーマットして入れる --}}
    @if($editData[$id] instanceof Carbon\Carbon)
    value="{{$editData[$id]->format('Y/m/d')}}" 
    @else
    value="{{$editData[$id]}}" 
    @endif
    @endisset

    {{-- maxlengthは固定 --}} 
    maxlength="10"
    {{-- 自動補完offにする --}} 
    autocomplete="off"
    >

    {{-- 実データは別途、hiddenに保持する。フォーマットに影響してしまう --}}
    <input type="hidden" 
    id="{{ $id }}" v-model="form.{{ $id }}" 
    {{-- MySQLのフォーマットで大丈夫 value="2019-08-02" --}}
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
  </ul>

</div>