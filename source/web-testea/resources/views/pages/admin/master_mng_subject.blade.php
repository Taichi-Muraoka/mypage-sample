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
			<th>科目コード</th>
			<th>名称</th>
			<th>略称</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr v-for="item in paginator.data" v-cloak>
			<td>@{{item.subject_cd}}</td>
			<td>@{{item.name}}</td>
			<td>@{{item.short_name}}</td>
			<td>
				<x-button.list-edit vueHref="'{{ route('master_mng_subject-edit', '') }}/' + item.subject_cd" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop