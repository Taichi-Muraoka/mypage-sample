{{------------------------------------------
button 送信(承認)
--------------------------------------------}}

@props(['caption' => ''])

{{-- mr-3は編集と削除ボタン用。支障があれば明示的に指定したい --}}
<button type="button" class="btn btn-success ml-3" v-on:click="submitApproval">
  <i class="fas fa-paper-plane"></i>
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</button>