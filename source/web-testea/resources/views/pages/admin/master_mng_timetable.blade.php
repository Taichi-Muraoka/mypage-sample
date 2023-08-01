@extends('adminlte::page')

@section('title', '時間割マスタ管理')

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
            <x-input.select caption="時間割区分" id="timetable_kind" :select2=true :select2Search=false>
                <option value="0">通常</option>
                <option value="1">特別期間</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
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
			<th width="7%"></th>
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
                <x-button.list-edit href="{{ route('master_mng_timetable-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop