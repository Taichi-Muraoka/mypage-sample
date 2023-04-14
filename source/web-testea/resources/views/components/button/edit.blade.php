{{------------------------------------------ 
    button 編集
  --------------------------------------------}}

@props(['vueHref' => '', 'href' => '', 'caption' => '', 
  'small' => false, 'icon' => 'fas fa-edit', 'vShow' => ''])

@if (!empty($vueHref))

{{-- vue用のbindを使用 --}}
<a :href="{{ $vueHref }}" class="btn btn-primary @if ($small) btn-sm @endif"
    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
    >
  @if (!empty($icon))<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '編集' }}@else{{ $caption }}@endif
</a>

@else

{{-- 普通のhref --}}
<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" class="btn btn-primary ml-3 @if ($small) btn-sm @endif"
    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
    >
  @if (!empty($icon))<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '編集' }}@else{{ $caption }}@endif
</a>

@endif