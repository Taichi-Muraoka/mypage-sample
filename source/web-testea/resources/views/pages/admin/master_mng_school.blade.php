@extends('adminlte::page')

@section('title', '校舎マスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

	<x-bs.row>
		<x-bs.col2>
			<x-input.select caption="コード区分" id="codecls" :select2=true/>
		</x-bs.col2>
	</x-bs.row>

</x-bs.card>

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
	<x-slot name="tools">
		<x-button.new caption="汎用マスタ取込" href="{{ route('master_mng_school-import') }}" :small=true />
	</x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="10%">コード区分</th>
			<th width="5%">コード</th>
			<th width="5%">数値1</th>
			<th width="5%">数値2</th>
			<th width="5%">数値3</th>
			<th width="5%">数値4</th>
			<th width="5%">数値5</th>
			<th width="10%">名称1</th>
			<th width="10%">名称2</th>
			<th width="10%">名称3</th>
			<th width="10%">名称4</th>
			<th width="10%">名称5</th>
			<th width="10%">表示順</th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>101</td>
			<td>110</td>
			<td>1</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>久我山校</td>
			<td>久我山</td>
			<td>久</td>
			<td></td>
			<td></td>
			<td>20</td>
		</tr>
		<tr>
			<td>101</td>
			<td>120</td>
			<td>1</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>西永福校</td>
			<td>西永福</td>
			<td>西</td>
			<td></td>
			<td></td>
			<td>50</td>
		</tr>
		<tr>
			<td>101</td>
			<td>130</td>
			<td>1</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>本郷校</td>
			<td>本郷山</td>
			<td>本</td>
			<td></td>
			<td></td>
			<td>30</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop