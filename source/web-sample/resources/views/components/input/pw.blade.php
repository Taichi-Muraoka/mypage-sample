{{------------------------------------------ 
    input - password
  --------------------------------------------}}

{{-- バリデートルールを解析する処理 --}}
@inject('bladeInputRule', 'App\Libs\BladeInputRule')

@props(['caption' => '', 'id' => '', 'placeholder' => '', 'editData' => [], 'rules' => []])

{{-- バリデーションエラー時のスクロール先 --}}
<span class="form-validation" data-id="{{ $id }}"></span>

<div class="form-group">

  {{-- ラベル --}}
  @if (!empty($caption))
  <label for="{{ $id }}"><span class="input-title">{{ $caption }}</span></label>
  @endif

  <input type="password" class="form-control" id="{{ $id }}" 
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
  >

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
  </ul>
</div>