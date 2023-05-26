@extends('adminlte::page')

@section('title', '指導ブースマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_booth-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="15%">指導ブースID</th>
			<th width="15%">校舎</th>
			<th width="15%">指導ブースコード</th>
			<th width="15%">名称</th>
			<th width="10%">表示順</th>
			<th width="15%">cat</th>
			<th width="15%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>久我山</td>
			<td>110</td>
			<td>Aテーブル</td>
			<td>10</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_booth-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>002</td>
			<td>久我山</td>
			<td>111</td>
			<td>Bテーブル</td>
			<td>11</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_booth-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>003</td>
			<td>久我山</td>
			<td>112</td>
			<td>Cテーブル</td>
			<td>12</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_booth-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_booth-modal')

@stop