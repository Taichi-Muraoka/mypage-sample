@extends('adminlte::page')

@section('title', '授業教材マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_text-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>教材コード</th>
			<th>学年</th>
			<th>授業科目コード</th>
			<th>教材科目コード</th>
			<th>名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>07101001</td>
			<td>中1</td>
			<td>101（英語）</td>
			<td>101（英語）</td>
			<td>中1英語基礎テキスト</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_text-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>07501201</td>
			<td>中1</td>
			<td>501（数学・英語）</td>
			<td>102（数学）</td>
			<td>中1数学ドリル</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_text-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>07102099</td>
			<td>中1</td>
			<td>102（数学）</td>
			<td>102（数学）</td>
			<td>中1その他</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_text-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop