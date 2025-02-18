{{------------------------------------------ 
    button exec 処理前に確認ダイアログを出す
    基本的にはモーダルを表示する想定
  --------------------------------------------}}

{{--
  caption: ボタン表示名
  dataTarget: モーダルのIDを指定 Def: #modal-dtl
  vueDisabled: Vueの使用不可条件
  vueDataAttr: data属性を定義
  dataAttr: data属性を定義
  class: 追加のクラス
  icon: アイコン
  small: 小さいボタンにするかどうか
  disabled: 使用不可 Def: false
--}}
@props(['dataTarget' => '', 'vueDataAttr' => [], 'dataAttr' => [], 'vueDisabled' => '', 'caption' => '', 'class' => '', 
  'icon' => 'fas fa-paper-plane', 'small' => false, 'disabled' => false, 'isIcon'=> false])

<button type="button" 
  class="btn btn-success ml-3 @if (!empty($class)){{ $class }}@endif @if ($small) btn-sm @endif" 
  data-toggle="modal"

  {{-- vue.jsのdisabled追加 --}}
  @if (!empty($vueDisabled)) v-bind:disabled="{{ $vueDisabled }}" @endif

  {{-- 開くモーダルを指定。動的に指定する場合は、vueDataAttr=['target' => 'xxx'] のように指定するのでそれ以外の場合 --}} 
  @if (!isset($vueDataAttr['target']))
  data-target="@if (empty($dataTarget)){{ '#modal-dtl' }}@else{{ $dataTarget }}@endif" 
  @endif

  {{-- buttonに対するdata属性の定義。vueで取得する用。 --}}
  @foreach($vueDataAttr as $key => $val)
   :data-{{$key}}="{{ $vueDataAttr[$key] }}"
  @endforeach

  {{-- buttonに対するdata属性の定義。vueをつかわない場合。bladeから直接の場合。模試申込者一覧など --}}
  @foreach($dataAttr as $key => $val)
   data-{{$key}}="{{ $dataAttr[$key] }}"
  @endforeach

  {{-- disabled追加 --}}
  @if ($disabled) {{ 'disabled' }} @endif
>
  @if ($isIcon)<i class="{{ $icon }}"></i>@endif
  @if (empty($caption)){{ '送信' }}@else{{ $caption }}@endif
</button>
