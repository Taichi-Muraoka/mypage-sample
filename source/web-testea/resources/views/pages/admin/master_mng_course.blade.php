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
			<th width="30%">名称</th>
			<th width="30%">コース種別</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>10100</td>
			<td>個別指導コース</td>
			<td>授業単</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>10200</td>
			<td>演習</td>
			<td>授業単</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>20100</td>
			<td>１対２</td>
			<td>授業複</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>20200</td>
			<td>１対３</td>
			<td>授業複</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>20300</td>
			<td>集団指導</td>
			<td>授業複</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>90100</td>
			<td>自習</td>
			<td>その他</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>90200</td>
			<td>面談</td>
			<td>その他</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_course-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop