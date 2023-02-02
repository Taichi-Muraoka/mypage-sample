@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    申込者一覧をファイル出力し、一覧に表示された申込者に受付メッセージを自動送信します。<br>
    よろしいですか？
</p>

@overwrite