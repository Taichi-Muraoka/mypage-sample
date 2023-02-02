@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    一覧に表示された申込者のイベント参加スケジュールを登録します。（ステータスが「受付」の生徒のみ）<br>
    よろしいですか？
</p>

@overwrite