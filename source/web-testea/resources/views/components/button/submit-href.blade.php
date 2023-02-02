{{------------------------------------------ 
    button 送信 hrefを指定
  --------------------------------------------}}

{{-- MEMO: 研修受講のダウンロードや視聴用向けに作成 --}}
@props(['caption' => '', 'class' => '', 'href' => '', 'blank' => false, 
  'icon' => 'fas fa-paper-plane', 'onClick' => '', 'onClickPrevent' => '', 'btn' => 'success', 'small' => false])

<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" 
  @if ($blank) target="_blank" @endif
  class="btn btn-{{$btn}} @if (!empty($class)){{ $class }}@endif @if ($small) btn-sm @endif" role="button"  
  {{-- クリックイベントとリンク先を開く --}}
  @if (!empty($onClick))v-on:click="{{ $onClick }}"@endif
  {{-- クリックイベントのみ --}}
  @if (!empty($onClickPrevent))v-on:click.prevent="{{ $onClickPrevent }}"@endif
  >
  <i class="{{ $icon }}"></i>
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</a>

