@extends('adminlte::page')

@section('title', '校舎マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_school-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="15%">コード</th>
			<th width="20%">名称</th>
			<th width="15%">表示名称</th>
			<th width="10%">略称</th>
			<th width="10%">表示順</th>
			<th width="15%">状態</th>
			<th width="15%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>110</td>
			<td>久我山校</td>
			<td>久我山</td>
			<td>久</td>
			<td>20</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_school-edit') }}" />
			</td>
		</tr>
		<tr>
			<td>120</td>
			<td>西永福校</td>
			<td>西永福</td>
			<td>西</td>
			<td>50</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_school-edit') }}" />
			</td>
		</tr>
		<tr>
			<td>130</td>
			<td>本郷校</td>
			<td>本郷山</td>
			<td>本</td>
			<td>30</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_school-edit') }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_school-modal')

@stop