@extends('adminlte::page')

@section('title', '成績科目マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_grade_subject-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
    <x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="30%">成績科目コード</th>
			<th width="30%">学校区分</th>
			<th width="30%">名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>小学校</td>
			<td>国語</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>002</td>
			<td>小学校</td>
			<td>算数</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>003</td>
			<td>小学校</td>
			<td>理科</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>004</td>
			<td>小学校</td>
			<td>社会</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>005</td>
			<td>小学校</td>
			<td>英語</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>110</td>
			<td>中学校</td>
			<td>数学</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>259</td>
			<td>高校</td>
			<td>数A</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_grade_subject-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop