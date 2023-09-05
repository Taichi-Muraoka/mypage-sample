@extends('adminlte::page')

@section('title', 'システムマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- テーブル --}}
    <x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>システム変数ID</th>
			<th>名称</th>
			<th>値（数値）</th>
			<th>値（文字列）</th>
			<th>画面変更可否</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>101</td>
			<td>事務作業時給</td>
			<td class="text-right">1072</td>
			<td></td>
			<td>可</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_system-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>111</td>
			<td>振替調整スキップ回数</td>
			<td class="text-right">2</td>
			<td></td>
			<td>可</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_system-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>201</td>
			<td>現年度</td>
			<td class="text-right">2023</td>
			<td></td>
			<td>不可</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_system-edit',1) }}" disabled=true />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop