@extends('adminlte::page')

@section('title', 'システムマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- テーブル --}}
	<x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>システム変数ID</th>
			<th>名称</th>
			<th>値</th>
			<th>画面変更可否</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr v-for="item in paginator.data" v-cloak>
			{{-- MEMO: 日付フォーマットを指定する --}}
			<td>@{{item.key_id}}</td>
			<td>@{{item.name}}</td>
			<td>@{{item.value_num}}@{{item.value_str}}@{{item.value_date}}</td>
			<td>@{{item.change_flg_name}}</td>
			<td>
				{{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
				<x-button.list-edit vueHref="'{{ route('master_mng_system-edit', '') }}/' + item.key_id" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop