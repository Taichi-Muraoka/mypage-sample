{{------------------------------------------ 
    button 追加
  --------------------------------------------}}

@props(['href' => '', 'caption' => '', 'small' => false])

{{-- 新規登録はモーダルはない想定なので、アンカーで対応 --}}
{{-- <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
  data-target="@if (empty($dataTarget)){{ '#modal-new' }}@else{{ $dataTarget }}@endif">
<i class="fas fa-plus"></i>
</button> --}}

<a href="@if (empty($href)){{ '#' }}@else{{ $href }}@endif" class="btn btn-default ml-2 @if ($small) btn-sm @endif" role="button">
  <i class="fas fa-plus"></i>
  @if (empty($caption)){{ '新規登録' }}@else{{ $caption }}@endif
</a>