@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    給与算出情報を出力します。<br>
    よろしいですか？
</p>

@overwrite