@extends('adminlte::page')

@section('title', 'コースマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_course-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="20%">コースコード</th>
			<th width="60%">名称</th>
			<th width="10%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>101</td>
			<td>個別指導</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>201</td>
			<td>集団授業</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_course-modal')

@stop