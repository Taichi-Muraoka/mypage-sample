@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    自動コマ組み処理を実行します。<br>
    よろしいですか？
</p>

@overwrite