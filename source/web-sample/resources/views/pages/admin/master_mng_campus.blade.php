@extends('adminlte::page')

@section('title', '校舎マスタ管理')

@section('content')

<x-bs.card-list>

	{{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('master_mng_campus-new') }}" :small=true />
    </x-slot>

	{{-- テーブル --}}
    <x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>校舎コード</th>
			<th>名称</th>
			<th>略称</th>
			<th>校舎メールアドレス</th>
			<th>校舎電話番号</th>
			<th>表示順</th>
			<th>非表示フラグ</th>
            <th></th>
		</x-slot>

		{{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.campus_cd}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.short_name}}</td>
            <td>@{{item.email_campus}}</td>
			<td>@{{item.tel_campus}}</td>
			<td>@{{item.disp_order}}</td>
			<td>@{{item.is_hidden_name}}</td>
            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('master_mng_campus-edit', '') }}/' + item.campus_cd" />
            </td>
        </tr>

	</x-bs.table>
</x-bs.card-list>

@stop