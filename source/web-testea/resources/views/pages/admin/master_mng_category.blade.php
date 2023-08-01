@extends('adminlte::page')

@section('title', '授業単元分類マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_category-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>単元分類コード</th>
			<th>学年</th>
			<th>教材科目コード</th>
			<th>名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>0710201</td>
			<td>中1</td>
			<td>102（数学）</td>
			<td>正負の数</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_category-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>0710202</td>
			<td>中1</td>
			<td>102（数学）</td>
			<td>方程式</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_category-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>0710299</td>
			<td>中1</td>
			<td>102（数学）</td>
			<td>その他</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_category-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop