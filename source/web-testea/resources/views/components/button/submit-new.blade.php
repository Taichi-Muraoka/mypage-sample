{{------------------------------------------ 
    button 送信
  --------------------------------------------}}

@props(['caption' => '', 'class' => '', 'isIcon'=> false])

<button type="button" class="btn btn-success @if (!empty($class)){{ $class }}@endif" v-on:click="submitNew">
  @if ($isIcon)<i class="fas fa-paper-plane"></i>@endif
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</button>