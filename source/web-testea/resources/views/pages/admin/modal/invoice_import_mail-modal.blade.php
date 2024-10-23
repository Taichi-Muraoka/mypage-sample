@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    @{{$filters.formatYmString(item.invoice_date)}}分の請求書発行通知メール一括送信処理を行います。
    （メール送信処理は1回のみ可能）<br>
    送信してよろしいですか？
</p>

@overwrite