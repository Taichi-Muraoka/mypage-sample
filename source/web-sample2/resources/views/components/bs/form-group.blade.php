{{------------------------------------------ 
    form-group
  --------------------------------------------}}

{{--
  id: ID
--}}
@props(['name' => ''])

{{-- スクロール時のエラー --}}
@if (!empty($name))
{{-- バリデーションエラー時のスクロール先 --}}
<span class="form-validation" data-id="{{ $name }}"></span>
@endif

<div class="form-group">
  {{ $slot }}
</div>

{{-- バリデート結果のエラー --}}
@if (!empty($name))
<ul class="err-list" v-cloak>
  <li v-for="msg in form_err.msg.{{ $name }}">
    @{{ msg }}
  </li>
</ul>
@endif