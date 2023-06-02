@extends('adminlte::page')

@section('title', '時間割マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_timetable-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="15%">時間割ID</th>
			<th width="15%">校舎</th>
			<th width="10%">時限</th>
			<th width="15%">開始時刻</th>
			<th width="15%">終了時刻</th>
			<th width="15%">時間割区分</th>
			<th width="10%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>久我山</td>
			<td>3限</td>
			<td>15:00</td>
			<td>16:30</td>
			<td>特別期間</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_timetable-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>002</td>
			<td>久我山</td>
			<td>4限</td>
			<td>16:45</td>
			<td>18:15</td>
			<td>通常</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_timetable-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_timetable-modal')

@stop