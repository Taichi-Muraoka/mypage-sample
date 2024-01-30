@extends('adminlte::page')

@section('title', '授業単元マスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

	<x-bs.row>
		<x-bs.col2>
			<x-input.select id="grade_cd" caption="学年" :select2=true onChange="selectChangeGetCategory"
				:mastrData=$grades :editData=$editData :select2Search=false :blank=true />
		</x-bs.col2>
		<x-bs.col2>
			<x-input.select id="t_subject_cd" caption="教材科目" :select2=true onChange="selectChangeGetCategory"
				:mastrData=$subjects :editData=$editData :select2Search=false :blank=true />
		</x-bs.col2>
	</x-bs.row>
	<x-bs.row>
		<x-bs.col2>
			<x-input.select id="unit_category_cd" caption="単元分類" :select2=true :editData=$editData :select2Search=true
				:blank=true>
				<option v-for="item in selectGetItemCategory.categories" :value="item.code">
					@{{ item.value }}
				</option>
			</x-input.select>
		</x-bs.col2>
	</x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

	{{-- カードヘッダ右 --}}
	<x-slot name="tools">
		<x-button.new href="{{ route('master_mng_unit-new') }}" :small=true />
	</x-slot>

	{{-- テーブル --}}
	<x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>学年</th>
			<th>教材科目</th>
			<th>単元分類</th>
			<th>単元コード</th>
			<th>名称</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr v-for="item in paginator.data" v-cloak>
			<td>@{{item.grade_name}}</td>
			<td>@{{item.subject_name}}</td>
			<td>@{{item.category_name}}</td>
			<td>@{{item.unit_cd}}</td>
			<td>@{{item.name}}</td>
			<td>
				{{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
				<x-button.list-edit vueHref="'{{ route('master_mng_unit-edit', '') }}/' + item.unit_id" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop