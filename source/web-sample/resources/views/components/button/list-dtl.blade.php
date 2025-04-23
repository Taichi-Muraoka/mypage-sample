{{------------------------------------------ 
    button リストの詳細
  --------------------------------------------}}

{{--
  caption: ボタン表示名
  dataTarget: モーダルのIDを指定 Def: #modal-dtl
  href: リンクへの遷移
  vueDataAttr: data属性を定義(vue)
  dataAttr: data属性を定義
  btn: buttonの色 Def: btn-secondary
  disabled: 使用不可 Def: false
  vueDisabled: Vueの使用不可条件
  vueHref: vue用のhrefのbind
  vueClick: Clickイベントを定義
--}}
@props(['dataTarget' => '', 'href' => '', 'vueDataAttr' => [], 'dataAttr' => [], 
  'caption' => '', 'btn' => '', 'disabled' => false, 'class' => '', 'vueDisabled' => '', 'vueHref' => '', 'vueClick' => ''])

@if (empty($href) && empty($vueHref))

<button type="button" 
  class="btn btn-sm @if (empty($btn)){{ 'btn-secondary' }}@else{{ $btn }}@endif @if ($disabled) {{ 'disabled' }} @endif @if (!empty($class)){{ $class }}@endif" 
  data-toggle="modal"

  {{-- 開くモーダルを指定。動的に指定する場合は、vueDataAttr=['target' => 'xxx'] のように指定するのでそれ以外の場合 --}} 
  @if (!isset($vueDataAttr['target']))
  data-target="@if (empty($dataTarget)){{ '#modal-dtl' }}@else{{ $dataTarget }}@endif" 
  @endif

  {{-- buttonに対するdata属性の定義。vueで取得する用。 --}}
  @foreach($vueDataAttr as $key => $val)
   :data-{{$key}}="{{ $vueDataAttr[$key] }}"
  @endforeach

  {{-- buttonに対するdata属性の定義。vueをつかわない場合。bladeから直接の場合。会員情報詳細など --}}
  @foreach($dataAttr as $key => $val)
   data-{{$key}}="{{ $dataAttr[$key] }}"
  @endforeach

  {{-- Vueの場合 --}}
  @if (!empty($vueDisabled)) :disabled="{{ $vueDisabled }}" @endif
  {{-- Bladeの場合 --}}
  @if ($disabled) {{ 'disabled' }} @endif

  @if (!empty($vueClick)) v-on:click="{{ $vueClick }}" @endif
>
  @if (empty($caption)){{ '詳細' }}@else{{ $caption }}@endif
</button>

@elseif (!empty($vueHref))

{{-- vue用のbindを使用 --}}
<a :href="{{ $vueHref }}" 
  class="btn @if (empty($btn)){{ 'btn-secondary' }}@else{{ $btn }}@endif btn-sm @if (!empty($class)){{ $class }}@endif @if ($disabled) {{ 'disabled' }} @endif" role="button">
  @if (empty($caption)){{ '詳細' }}@else{{ $caption }}@endif
</a>

@else

{{-- 単純なリンクの遷移 --}}
<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" 
  class="btn @if (empty($btn)){{ 'btn-secondary' }}@else{{ $btn }}@endif btn-sm @if (!empty($class)){{ $class }}@endif @if ($disabled) {{ 'disabled' }} @endif" role="button">
  @if (empty($caption)){{ '詳細' }}@else{{ $caption }}@endif
</a>

@endif