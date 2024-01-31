{{------------------------------------------ 
    input - hidden
  --------------------------------------------}}

{{-- 
  editData: 編集用のデータ
  validateErr: バリデート結果のエラー表示
--}}
@props(['id' => '', 'editData' => [],'validateErr' => false])

<input type="hidden" id="{{ $id }}" v-model="form.{{ $id }}" 
{{-- 編集時にデータをセット --}} 
@isset($editData[$id])
  value="{{$editData[$id]}}" 
@endisset>
{{-- バリデート結果のエラー --}}
@if ($validateErr)
<ul class="err-list" v-cloak>
  <li v-for="msg in form_err.msg.{{ $id }}">
    @{{ msg }}
  </li>
</ul>
@endif
