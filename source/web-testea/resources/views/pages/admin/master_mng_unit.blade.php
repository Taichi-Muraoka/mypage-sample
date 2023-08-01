@extends('adminlte::page')

@section('title', '授業単元マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_unit-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>単元ID</th>
			<th>単元分類コード</th>
			<th>単元コード</th>
			<th>名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>0710201（正負の数）</td>
			<td>01</td>
			<td>負の数とは</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_unit-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>002</td>
			<td>0710201（正負の数）</td>
			<td>99</td>
			<td>その他</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_unit-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>003</td>
			<td>0710202（方程式）</td>
			<td>01</td>
			<td>方程式とは</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_unit-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop