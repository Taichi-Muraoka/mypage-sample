@extends('adminlte::page')

@section('title', 'システムマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="15%">システム変数ID</th>
			<th width="30%">名称</th>
			<th width="20%">値（数値）</th>
			<th width="20%">値（文字列）</th>
			<th width="10%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>101</td>
			<td>事務作業時給</td>
			<td>1000</td>
			<td></td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_system-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>111</td>
			<td>振替調整スキップ回数</td>
			<td>2</td>
			<td></td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_system-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_system-modal')

@stop