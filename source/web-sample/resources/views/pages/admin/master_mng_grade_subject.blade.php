@extends('adminlte::page')

@section('title', '成績科目マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
	<x-slot name="tools">
		<x-button.new href="{{ route('master_mng_grade_subject-new') }}" :small=true />
	</x-slot>

	{{-- テーブル --}}
	<x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="30%">成績科目コード</th>
			<th width="30%">学校区分</th>
			<th width="30%">名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr v-for="item in paginator.data" v-cloak>
			<td>@{{item.g_subject_cd}}</td>
			<td>@{{item.school_kind_name}}</td>
			<td>@{{item.name}}</td>
			<td>
				{{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
				<x-button.list-edit vueHref="'{{ route('master_mng_grade_subject-edit', '') }}/' + item.g_subject_cd" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop