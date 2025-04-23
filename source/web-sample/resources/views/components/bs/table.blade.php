{{------------------------------------------ 
    table
  --------------------------------------------}}

{{-- 
    vHeader: 縦がタイトル行の場合 
    button: 一番右の列がbuttonの列の場合
    hover: ホバーを有効にするかどうか
    class: 追加のクラス
    bordered: 線を引くかどうか
    smartPhone: テーブルの縦並びの有無
    smartPhoneModal: モーダルのフォントサイズ調整・個別調整の有無
    vShow: Vue.jsのv-show
--}}
@props(['button' => false, 'hover' => true, 'vHeader' => false, 'class' => '', 'bordered' => true, 
  'smartPhone' => false, 'smartPhoneModal' => false, 'vShow' => ''])

<table class="table @if ($bordered) table-bordered @endif
    @if ($hover) table-hover @endif @if ($button) table-button @endif @if ($vHeader) table-v-header 
    @endif @if (!empty($class)){{ $class }}@endif @if ($smartPhone) smart-phone @endif @if ($smartPhoneModal) smart-phone-modal @endif"
    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
    >
    @isset($thead)
    <thead>
        <tr>
            {{ $thead }}
        </tr>
    </thead>
    @endisset
    <tbody>
        {{ $slot }}
    </tbody>
</table>