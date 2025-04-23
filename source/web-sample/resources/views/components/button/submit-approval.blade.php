{{------------------------------------------
button 送信(承認)
--------------------------------------------}}

@props(['caption' => '', 'disabled' => false, 'class' => '', 'vueDisabled' => '', 'isIcon'=> false])

{{-- mr-3は編集と削除ボタン用。支障があれば明示的に指定したい --}}
<button type="button" class="btn btn-success ml-3" v-on:click="submitApproval"
  {{-- Vueの場合 --}}
  @if (!empty($vueDisabled)) :disabled="{{ $vueDisabled }}" @endif
  {{-- Bladeの場合 --}}
  @if ($disabled) {{ 'disabled' }} @endif
>
  @if ($isIcon)<i class="fas fa-paper-plane"></i>@endif
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</button>