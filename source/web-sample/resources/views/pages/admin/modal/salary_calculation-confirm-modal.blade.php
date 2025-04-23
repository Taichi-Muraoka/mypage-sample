@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    @{{$filters.formatYmString(item.salary_date)}}分の確定処理を行い、対象月の追加請求を「支払済」とします。<br>
    よろしいですか？
</p>

@overwrite