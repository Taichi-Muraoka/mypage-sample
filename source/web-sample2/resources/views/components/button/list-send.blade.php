{{------------------------------------------ 
    button リストの送信
  --------------------------------------------}}

@props(['href' => '', 'caption' => '', 'icon' => '', 'vueHref' => ''])

@if (empty($vueHref))
<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" class="btn btn-success btn-sm" role="button">
  @if (!empty($icon))<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</a>
@else
{{-- vue用のbindを使用 --}}
<a :href="{{ $vueHref }}" class="btn btn-success btn-sm" role="button">
  @if (!empty($icon))<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</a>
@endif