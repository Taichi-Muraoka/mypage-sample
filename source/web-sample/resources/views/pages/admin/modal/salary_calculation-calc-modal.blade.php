@extends('pages.common.modal')

@section('modal-size')
{{-- デフォルトにしたいのでとりあえずなんか指定しておく --}}
modal-normal
@overwrite

@section('modal-body')

<p>
    @{{$filters.formatYmString(item.salary_date)}}分の給与集計処理を行います。<br>
    よろしいですか？
</p>

@overwrite