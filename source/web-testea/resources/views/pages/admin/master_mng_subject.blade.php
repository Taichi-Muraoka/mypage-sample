@extends('adminlte::page')

@section('title', '教科マスタ管理')

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
			<th>コード</th>
			<th>学校区分</th>
			<th>名称</th>
			<th>表示順</th>
			<th>状態</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>小</td>
			<td>国語</td>
			<td>1</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_subject-edit') }}" />
			</td>
		</tr>
		<tr>
			<td>110</td>
			<td>中</td>
			<td>国数</td>
			<td>10</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_subject-edit') }}" />
			</td>
		</tr>
		<tr>
			<td>259</td>
			<td>高</td>
			<td>英語</td>
			<td>50</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_subject-edit') }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_subject-modal')

@stop