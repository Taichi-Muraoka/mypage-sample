{{------------------------------------------ 
    button 追加
  --------------------------------------------}}
{{--
  href: リンクへの遷移
  small: 小さいボタンにするかどうか
  caption: ボタン表示名
  btn: buttonの色 Def: btn-default
  disabled: 使用不可 Def: false
--}}

@props(['href' => '', 'caption' => '', 'btn' => '', 'small' => false, 'disabled' => false])

{{-- 新規登録はモーダルはない想定なので、アンカーで対応 --}}
{{-- <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
  data-target="@if (empty($dataTarget)){{ '#modal-new' }}@else{{ $dataTarget }}@endif">
<i class="fas fa-plus"></i>
</button> --}}

<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif"
  class="btn @if (empty($btn)){{ 'btn-default' }}@else{{ $btn }}@endif ml-2 @if ($small) btn-sm @endif @if ($disabled) {{ 'disabled' }} @endif"
  role="button">
  <i class="fas fa-plus"></i>
  @if (empty($caption)){{ '新規登録' }}@else{{ $caption }}@endif
</a>