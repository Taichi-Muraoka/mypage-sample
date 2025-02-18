{{------------------------------------------ 
    callout
  --------------------------------------------}}

@props(['title' => '', 'type' => 'info', 'margin' => true])

<div class="callout callout-{{ $type }} @if ($margin) mt-4 mb-4 @endif">
    {{-- タイトル --}}
    @if (!empty($title))
    <h6>{{ $title }}</h6>
    @endif
    {{-- 本文 --}}
    <p>{{ $slot }}</p>
</div>