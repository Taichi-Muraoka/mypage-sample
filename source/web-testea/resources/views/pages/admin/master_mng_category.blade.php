@extends('adminlte::page')

@section('title', '授業単元分類マスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="cls_cd" caption="学年" :select2=true >
                <option value="1">中1</option>
                <option value="2">中2</option>
                <option value="3">中3</option>
                <option value="4">高1</option>
                <option value="5">高2</option>
                <option value="6">高3</option>
            </x-input.select>
        </x-bs.col2>
		<x-bs.col2>
            <x-input.select id="t_subject_cd" caption="教材科目" :select2=true >
                <option value="1">英語</option>
                <option value="2">数学</option>
                <option value="3">国語</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_category-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
	<x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th width="20%">単元分類コード</th>
			<th width="10%">学年</th>
			<th width="20%">教材科目</th>
			<th>名称</th>
			<th width="7%"></th>
		</x-slot>

		{{-- テーブル行 --}}
		<tr>
			<td>0710201</td>
			<td>中1</td>
			<td>数学</td>
			<td>正負の数</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_category-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>0710202</td>
			<td>中1</td>
			<td>数学</td>
			<td>方程式</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_category-edit',1) }}" />
			</td>
		</tr>
		<tr>
			<td>0710299</td>
			<td>中1</td>
			<td>数学</td>
			<td>その他</td>
			<td>
                <x-button.list-edit href="{{ route('master_mng_category-edit',1) }}" />
			</td>
		</tr>

	</x-bs.table>
</x-bs.card-list>

@stop