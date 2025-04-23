@extends('pages.common.modal')

@section('modal-title','モーダルフォーム更新')

@section('modal-body')

{{-- 件名 --}}
<x-input.text caption="サンプル件名" id="sample_title" :rules=$rulesExec />
{{-- 本文 --}}
<x-input.textarea caption="サンプルテキスト" id="sample_text" :rules=$rulesExec />
{{-- ステータスリスト --}}
<x-input.select id="sample_state" caption="ステータス" :editData=$editData :mastrData=$sampleStateList
:select2=true :select2Search=false :blank=false />

{{-- 選択されたIDを保持する --}}
<x-input.hidden id="sample_id" />

@overwrite
