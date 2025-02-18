{{------------------------------------------ 
    button 更新
  --------------------------------------------}}

@props(['caption' => '', 'isIcon'=> false])

{{--  mr-3は編集と削除ボタン用。支障があれば明示的に指定したい --}}
<button type="button" class="btn btn-success ml-3" v-on:click="submitEdit">
  @if ($isIcon)<i class="fas fa-paper-plane"></i>@endif
  @if (empty($caption)){{ '更新' }}@else{{ $caption }}@endif
</button>