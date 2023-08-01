@extends('adminlte::page')

@section('title', '校舎マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_campus-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>校舎コード</th>
			<th>名称</th>
			<th>略称</th>
			<th>校舎メールアドレス</th>
			<th>校舎電話番号</th>
			<th>表示順</th>
			<th>非表示フラグ</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>110</td>
			<td>久我山</td>
			<td>久</td>
			<td>kugayama@testea.test.com</td>
			<td>0311112222</td>
			<td>1</td>
			<td>表示</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_campus-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>120</td>
			<td>西永福</td>
			<td>西永</td>
			<td>nishieihuku@testea.test.com</td>
			<td>0333334444</td>
			<td>2</td>
			<td>表示</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_campus-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>130</td>
			<td>本郷</td>
			<td>本</td>
			<td>hongo@testea.test.com</td>
			<td>0355556666</td>
			<td>3</td>
			<td>非表示</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_campus-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop