@extends('adminlte::page')

@section('title', 'コースマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_course-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
    <x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="20%">コースコード</th>
			<th width="20%">名称</th>
            <th width="20%">略称</th>
			<th width="20%">コース種別</th>
			<th width="20%">給与集計種別</th>
			<th></th>
		</x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.course_cd}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.short_name}}</td>
            <td>@{{item.course_kind_name}}</td>
			<td>@{{item.summary_kind_name}}</td>
            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('master_mng_course-edit', '') }}/' + item.course_cd" />
            </td>
        </tr>

	</x-bs.table>
</x-bs.card-list>

@stop