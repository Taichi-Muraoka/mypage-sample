@extends('adminlte::page')

@section('title', '指導ブースマスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="roomcd" caption="校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="用途種別" id="usage_kind" :select2=true :select2Search=false>
                <option value="1">授業用</option>
                <option value="2">オンライン用</option>
				<option value="3">面談用</option>
				<option value="4">両者オンライン</option>
				<option value="5">家庭教師</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_booth-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>ブースID</th>
			<th>校舎</th>
			<th>ブースコード</th>
			<th>用途種別</th>
			<th>名称</th>
			<th>表示順</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>001</td>
			<td>久我山</td>
			<td>110</td>
			<td>授業用</td>
			<td>Aテーブル</td>
			<td>1</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_booth-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>002</td>
			<td>久我山</td>
			<td>111</td>
			<td>授業用</td>
			<td>Bテーブル</td>
			<td>2</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_booth-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>003</td>
			<td>久我山</td>
			<td>112</td>
			<td>授業用</td>
			<td>Cテーブル</td>
			<td>3</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_booth-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop