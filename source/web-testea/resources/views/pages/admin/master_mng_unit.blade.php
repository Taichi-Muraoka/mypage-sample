@extends('adminlte::page')

@section('title', '授業単元マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_unit-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="20%">単元ID</th>
			<th width="20%">学年</th>
			<th width="20%">科目</th>
			<th width="20%">名称</th>
			<th width="10%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>小1</td>
			<td>国語</td>
			<td>ひらがな</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_unit-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>110</td>
			<td>中1</td>
			<td>数学</td>
			<td>正負の数</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_unit-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>259</td>
			<td>高1</td>
			<td>英語</td>
			<td>自動詞と他動詞</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_unit-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_unit-modal')

@stop