@extends('adminlte::page')

@section('title', '授業単元分類マスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

	<x-bs.row>
		<x-bs.col2>
			<x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$grades :editData=$editData
				:select2Search=false :blank=true />
		</x-bs.col2>
		<x-bs.col2>
			<x-input.select id="t_subject_cd" caption="教材科目" :select2=true :mastrData=$subjects :editData=$editData
				:select2Search=false :blank=true />
		</x-bs.col2>
	</x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

	{{-- カードヘッダ右 --}}
	<x-slot name="tools">
		<x-button.new href="{{ route('master_mng_category-new') }}" :small=true />
	</x-slot>

	{{-- テーブル --}}
	<x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>単元分類コード</th>
			<th>学年</th>
			<th>教材科目</th>
			<th>名称</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr v-for="item in paginator.data" v-cloak>
			<td>@{{item.unit_category_cd}}</td>
			<td>@{{item.grade_name}}</td>
			<td>@{{item.subject_name}}</td>
			<td>@{{item.name}}</td>
			<td>
				{{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
				<x-button.list-edit vueHref="'{{ route('master_mng_category-edit', '') }}/' + item.unit_category_cd" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop