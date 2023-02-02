{{------------------------------------------ 
    button 検索
  --------------------------------------------}}

@props(['caption' => ''])

{{-- disabledは検索中に非活性にするため --}}
<button type="button" class="btn btn-success" v-on:click="btnSearch" v-bind:disabled="disabledBtnSearch">
  <i class="fas fa-search"></i>
  @if (empty($caption)){{ '検索' }}@else{{ $caption }}@endif
</button>