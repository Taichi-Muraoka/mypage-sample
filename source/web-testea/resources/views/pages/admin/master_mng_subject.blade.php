@extends('adminlte::page')

@section('title', '授業科目マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_subject-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
    <x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="45%">科目コード</th>
			<th width="45%">名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>101</td>
			<td>英語</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>102</td>
			<td>数学</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>103</td>
			<td>算数</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_subject-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop