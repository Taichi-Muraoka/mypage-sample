{{------------------------------------------ 
    button 送信
  --------------------------------------------}}

@props(['caption' => '', 'class' => ''])

<button type="button" class="btn btn-success @if (!empty($class)){{ $class }}@endif" v-on:click="submitNew">
  <i class="fas fa-paper-plane"></i>
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</button>