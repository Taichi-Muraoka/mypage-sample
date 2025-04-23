{{------------------------------------------ 
    input - timepicker
    今回はタイムピッカーは使用しないが、一応、時分の入力用としてこれを使用する。
    プレースホルダーが違うくらい。
    今後ピッカーを使用する可能性があるので明示的にこのファイルを使用する。
  --------------------------------------------}}

{{-- 
  editData: 編集用のデータ
  vShow: Vue.jsのv-show
--}}
@props(['caption' => '', 'id' => '', 'editData' => [], 'rules' => [], 'vShow' => ''])

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

  {{-- バリデート結果のエラー時に色を変える。v-bind --}}
  <input type="text" class="form-control" id="{{ $id }}" 
    {{-- 固定 --}}
    placeholder="例 09:00"
    {{-- vue --}}
    v-model="form.{{ $id }}" 
    v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} }" 

    {{-- 編集時にデータをセット --}} 
    @isset($editData[$id])
    {{-- Carbonならフォーマットして入れる --}}
    @if($editData[$id] instanceof Carbon\Carbon)
    value="{{$editData[$id]->format('H:i')}}" 
    @else
    value="{{$editData[$id]}}" 
    @endif
    @endisset
    
    {{-- maxlengthは固定 --}} 
    maxlength="5"
    >

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
  </ul>

</div>