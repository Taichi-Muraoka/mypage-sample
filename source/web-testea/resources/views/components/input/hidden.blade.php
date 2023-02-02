{{------------------------------------------ 
    input - text
  --------------------------------------------}}

{{-- 
  editData: 編集用のデータ
--}}
@props(['id' => '', 'editData' => []])

<input type="hidden" id="{{ $id }}" v-model="form.{{ $id }}" 
{{-- 編集時にデータをセット --}} 
@isset($editData[$id])
  value="{{$editData[$id]}}" 
@endisset>