@extends('adminlte::page')

@section('title', '契約コースマスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_agreement-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>契約コースコード</th>
			<th>授業種別</th>
			<th>学校区分</th>
			<th>名称</th>
			<th>金額</th>
			<th>単価</th>
			<th>回数</th>
			<th></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>101</td>
			<td>個別</td>
			<td>中</td>
			<td>個別指導 中学生コース（受験準備学年）</td>
			<td>33,880</td>
			<td>8,470</td>
			<td>4</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_agreement-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>201</td>
			<td>集団</td>
			<td>中</td>
			<td>集団授業 中学生 英語・数学総復習パック</td>
			<td>50,000</td>
			<td>5,000</td>
			<td>10</td>
			<td>
				{{-- <x-button.list-dtl /> --}}
                <x-button.list-edit href="{{ route('master_mng_agreement-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.master_mng_agreement-modal')

@stop