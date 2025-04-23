{{------------------------------------------ 
    button 戻る
  --------------------------------------------}}

@props(['caption' => '', 'url' => ''])

{{-- 子画面として親URLへ遷移する --}}
<a href="@if (empty($url)){{ Request::root()}}/{{ Request::segment(1) }}@else{{ $url }}@endif" class="btn btn-default">
  <i class="fas fa-arrow-left"></i>
  @if (empty($caption)){{ '戻る' }}@else{{ $caption }}@endif
</a>

