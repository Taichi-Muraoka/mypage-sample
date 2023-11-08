{{------------------------------------------ 
    button 編集
  --------------------------------------------}}
{{--
  caption: ボタン表示名
  href: リンクへの遷移
  vueHref: vue用のhrefのbind
  btn: buttonの色 Def: btn-primary
--}}

@props(['vueHref' => '', 'href' => '', 'caption' => '', 'btn' => '', 
  'small' => false, 'icon' => 'fas fa-edit', 'vShow' => '', 'disabled' => false])

@if (!empty($vueHref))

{{-- vue用のbindを使用 --}}
<a :href="{{ $vueHref }}" class="btn @if (empty($btn)){{ 'btn-primary' }}@else{{ $btn }}@endif @if ($small) btn-sm @endif"
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
<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" class="btn @if (empty($btn)){{ 'btn-primary' }}@else{{ $btn }}@endif ml-3 @if ($small) btn-sm @endif @if ($disabled) disabled @endif"
    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
    >
  @if (!empty($icon))<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '編集' }}@else{{ $caption }}@endif
</a>

@endif