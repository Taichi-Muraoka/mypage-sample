{{------------------------------------------
    button 送信 hrefを指定
  --------------------------------------------}}

{{-- MEMO: 保持期限超過データバックアップのダウンロード、研修受講のダウンロードや視聴用向けに作成 --}}
@props(['caption' => '', 'class' => '', 'vueHref' => '', 'href' => '', 'blank' => false, 'vueDisabled' => '',
  'icon' => 'fas fa-paper-plane', 'onClick' => '', 'onClickPrevent' => '', 'btn' => 'success', 'small' => false, 'isIcon'=> false])

@if (!empty($vueHref))

{{-- vue用のbindを使用 --}}
<a :href="{{ $vueHref }}"
  @if ($blank) target="_blank" @endif
  class="btn btn-{{$btn}} @if (!empty($class)){{ $class }}@endif @if ($small) btn-sm @endif" role="button"
  {{-- クリックイベントとリンク先を開く --}}
  @if (!empty($onClick))v-on:click="{{ $onClick }}"@endif
  {{-- クリックイベントのみ --}}
  @if (!empty($onClickPrevent))v-on:click.prevent="{{ $onClickPrevent }}"@endif
  {{-- vue.jsのdisabled追加 --}}
  @if (!empty($vueDisabled)) v-bind:class="{ disabled: {{ $vueDisabled }} }" @endif
  >
  <i class="{{ $icon }}"></i>
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</a>

@else

{{-- 普通のhref --}}
<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif"
  @if ($blank) target="_blank" @endif
  class="btn btn-{{$btn}} @if (!empty($class)){{ $class }}@endif @if ($small) btn-sm @endif" role="button"
  {{-- クリックイベントとリンク先を開く --}}
  @if (!empty($onClick))v-on:click="{{ $onClick }}"@endif
  {{-- クリックイベントのみ --}}
  @if (!empty($onClickPrevent))v-on:click.prevent="{{ $onClickPrevent }}"@endif
  >
  @if ($isIcon)<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</a>

@endif