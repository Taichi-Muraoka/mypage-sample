@extends('adminlte::page')

@section('title', '学年マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_grade-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>コード</th>
			<th>学校区分</th>
			<th>教科名称</th>
			<th>略称</th>
			<th>表示順</th>
			<th>状態</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>01</td>
			<td>小</td>
			<td>小学1年</td>
			<td>小1</td>
			<td>36</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_grade-edit') }}" />
			</td>
		</tr>
		<tr>
			<td>02</td>
			<td>小</td>
			<td>小学2年</td>
			<td>小2</td>
			<td>35</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_grade-edit') }}" />
			</td>
		</tr>
		<tr>
			<td>03</td>
			<td>小</td>
			<td>小学3年</td>
			<td>小3</td>
			<td>34</td>
			<td></td>
			<td>
				<x-button.list-dtl />
                <x-button.list-edit href="{{ route('master_mng_grade-edit') }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_grade-modal')

@stop