{{------------------------------------------
    スマホ対応のtable->tdタグ
  --------------------------------------------}}

  @props(['caption' => '', 'class' => '', 'colspan' => '', 'vShow' => ''])

<td @if (!empty($class)) class="{{ $class }}"@endif
    @if (!empty($colspan)) colspan="{{ $colspan }}"@endif
    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
><span class="resp-th">{{ $caption }}</span>{{ $slot }}</td>