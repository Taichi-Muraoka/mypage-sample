{{------------------------------------------
    input - text
  --------------------------------------------}}

{{-- バリデートルールを解析する処理 --}}
@inject('bladeInputRule', 'App\Libs\BladeInputRule')

{{--
  editData: 編集用のデータ
--}}
@props(['caption' => '', 'id' => '', 'placeholder' => '', 'editData' => [], 'rules' => [], 'vShow' => '',
'readOnly' => false, 'autoCompleteOff' => true])

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

  {{-- バリデート結果のエラー時に色を変える。v-bind --}}
  <input type="text" class="form-control" id="{{ $id }}"
    {{-- placeholderはデフォルトでcaptionにした --}}
    placeholder="@if (empty($placeholder)){{ $caption }}@else{{ $placeholder }}@endif"
    {{-- vue --}}
    v-model="form.{{ $id }}"
    v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }"
    {{-- 編集時にデータをセット --}}
    @isset($editData[$id])
    value="{{$editData[$id]}}"
    @endisset

    {{-- maxlengthを取得 --}}
    {{$bladeInputRule->getMaxLength($rules, $id)}}
    {{-- 数値キーボード入力に対応。以下は特別にエスケープしない！固定文字列しか来ないので。通常は!!は使わない --}}
    {!!$bladeInputRule->setNumKeyboard($rules, $id)!!}

    {{-- readonly --}}
    @if ($readOnly)
   readonly
    @endif
    {{-- 自動補完offにする --}}
    {{-- デフォルトoffとした。個別にoffとする場合は@propsの方を'autoCompleteOff' => falseとする --}}
    @if ($autoCompleteOff)
    autocomplete="off"
    @endif
    >

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
  </ul>

</div>