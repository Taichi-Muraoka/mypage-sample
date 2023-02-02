@extends('adminlte::page')

@section('title', 'マスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

	<x-bs.row>
		<x-bs.col2>
			<x-input.select caption="コード区分" id="codecls" :select2=true :mastrData=$extGenericMasters />
		</x-bs.col2>
	</x-bs.row>

</x-bs.card>

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
	<x-slot name="tools">
		<x-button.new caption="汎用マスタ取込" href="{{ route('master_mng-import') }}" :small=true />
	</x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="10%">コード区分</th>
			<th width="5%">コード</th>
			<th width="5%">数値1</th>
			<th width="5%">数値2</th>
			<th width="5%">数値3</th>
			<th width="5%">数値4</th>
			<th width="5%">数値5</th>
			<th width="10%">名称1</th>
			<th width="10%">名称2</th>
			<th width="10%">名称3</th>
			<th width="10%">名称4</th>
			<th width="10%">名称5</th>
			<th width="10%">表示順</th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr v-for="item in paginator.data" v-cloak>
			<td>@{{item.codecls}}</td>
			<td>@{{item.code}}</td>
			<td>@{{item.value1}}</td>
			<td>@{{item.value2}}</td>
			<td>@{{item.value3}}</td>
			<td>@{{item.value4}}</td>
			<td>@{{item.value5}}</td>
			<td>@{{item.name1}}</td>
			<td>@{{item.name2}}</td>
			<td>@{{item.name3}}</td>
			<td>@{{item.name4}}</td>
			<td>@{{item.name5}}</td>
			<td>@{{item.disp_order}}</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop