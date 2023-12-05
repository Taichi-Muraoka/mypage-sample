{{------------------------------------------ 
    button リストの更新
  --------------------------------------------}}

{{--
  caption: ボタン表示名
  href: リンクへの遷移
  vueHref: vue用のhrefのbind
  disabled: 使用不可 Def: false
  vueDisabled: Vueの使用不可条件
--}}
@props(['vueHref' => '', 'href' => '', 'caption' => '', 'disabled' => false, 'vueDisabled' => ''])

@if (!empty($vueHref))

{{-- vue用のbindを使用 --}}
<a :href="{{ $vueHref }}" 
  class="btn btn-primary btn-sm @if ($disabled) {{ 'disabled' }} @endif"
  {{-- vue.jsのdisabled追加 --}}
  @if (!empty($vueDisabled)) v-bind:class="{ disabled: {{ $vueDisabled }} }" @endif
  >
  @if (empty($caption)){{ '更新' }}@else{{ $caption }}@endif
</a>

@else

{{-- 普通のhref --}}
<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" 
  class="btn btn-primary btn-sm @if ($disabled) {{ 'disabled' }} @endif">
  @if (empty($caption)){{ '更新' }}@else{{ $caption }}@endif
</a>

@endif