{{------------------------------------------ 
    input - textarea
  --------------------------------------------}}

{{-- バリデートルールを解析する処理 --}}
@inject('bladeInputRule', 'App\Libs\BladeInputRule')

@props(['caption' => '', 'id' => '', 'placeholder' => '', 'rows' => 3, 'editData' => [], 'rules' => [],'vShow' => ''])

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

  <textarea id="{{ $id }}" class="form-control" rows="{{ $rows }}" 
    {{-- placeholderはデフォルトでcaptionにした --}}
    placeholder="@if (empty($placeholder)){{ $caption }}@else{{ $placeholder }}@endif"
    {{-- vue --}}
    v-model="form.{{ $id }}"
    v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }"
    {{-- maxlengthを取得 --}} 
    {{$bladeInputRule->getMaxLength($rules, $id)}}
    {{-- 編集時にデータをセット。スペースが入るので><の間は詰める --}}
    {{-- $slot:モック用 --}}
    >{{ $slot }}@isset($editData[$id]){{$editData[$id]}}@endisset</textarea>

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
  </ul>

</div>