@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>一覧画面に表示された成績情報を出力します。<br>
よろしいですか？</p>

@overwrite