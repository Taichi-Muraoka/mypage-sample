{{------------------------------------------ 
    button 削除送信(バリデーションチェックあり)
  --------------------------------------------}}
{{--
  caption: ボタン表示名
  disabled: 使用不可 Def: false
--}}
@props(['caption' => '', 'disabled' => false])

{{-- disabledの時はVueのクリックも無効にする  --}}
<button type="button" class="btn btn-danger @if ($disabled) {{ 'disabled' }} @endif" 
  @if (!$disabled) v-on:click="submitValidationDelete" @endif>
  <i class="fas fa-trash-alt"></i>
  @if (empty($caption)){{ '削除' }}@else{{ $caption }}@endif
</button>