@extends('pages.common.modal')

@section('modal-title','講師選択')

@section('modal-body')

<p>以下のリストより、担当講師を選択してください。</p>

    {{-- 講師リスト --}}
    <x-input.select id="tid" :select2=true >
      <option value="1">CW講師１０１</option>
      <option value="2">CW講師１０２</option>
      <option value="3">CW講師１０３</option>
      <option value="4">CW講師１０４</option>
      <option value="5">CW講師１０５</option>
    </x-input.select>

@overwrite

{{-- モーダルの追加のボタン --}}
@section('modal-button')

{{-- 確定ボタン --}}
<button type="button" class="btn btn-primary" data-dismiss="modal">確定</button>

@overwrite