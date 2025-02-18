@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>一覧画面に表示された超過勤務者一覧を出力します。<br>
よろしいですか？</p>

@overwrite