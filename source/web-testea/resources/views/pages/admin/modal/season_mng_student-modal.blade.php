@extends('pages.common.modal')

@section('modal-title','講師選択')

@section('modal-body')

<p>以下のリストより、担当講師を選択してください。</p>

{{-- 講師リスト --}}
<x-input.select id="tutor_id" :select2=true blankText="未選択" onChange="selectChange" :editData=$editData>
  <option v-for="item in selectGetItem" :value="item.id">
    @{{ item.value }}
  </option>
</x-input.select>

{{-- 選択された講師名を保持する --}}
<x-input.hidden id="tname" />

@overwrite
