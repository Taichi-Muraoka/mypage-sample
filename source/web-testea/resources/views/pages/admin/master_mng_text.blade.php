@extends('adminlte::page')

@section('title', '授業教材マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_text-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="20%">教材ID</th>
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
			<td>国語ドリル基礎</td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_text-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>110</td>
			<td>中1</td>
			<td>数学</td>
			<td>数学ドリル発展</td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_text-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>259</td>
			<td>高1</td>
			<td>英語</td>
			<td>コミュニケーション英語演習</td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_text-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_text-modal')

@stop