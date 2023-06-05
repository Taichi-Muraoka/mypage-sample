@extends('adminlte::page')

@section('title', '授業科目マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_subject-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="40%">科目コード</th>
			<th width="40%">名称</th>
			<th width="10%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>国語</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>002</td>
			<td>数学</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>003</td>
			<td>英語</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_subject-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_subject-modal')

@stop