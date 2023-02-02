{{------------------------------------------ 
    スマホ対応のtable->tdタグ
  --------------------------------------------}}

  @props(['caption' => '', 'class' => '', 'vShow' => ''])

<td @if (!empty($class)) class="{{ $class }}"@endif
    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
><span class="resp-th">{{ $caption }}</span>{{ $slot }}</td>