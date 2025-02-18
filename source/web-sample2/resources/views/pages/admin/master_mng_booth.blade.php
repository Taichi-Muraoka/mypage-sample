@extends('adminlte::page')

@section('title', 'ブースマスタ管理')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            {{-- ステータスや種別は、検索を非表示とする --}}
            <x-input.select id="usage_kind" caption="用途種別" :select2=true :mastrData=$kindList :editData=$editData
                :select2Search=false :blank=true />
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
    <x-bs.table :button=true>

		{{-- テーブルタイトル行 --}}
		<x-slot name="thead">
			<th>校舎</th>
			<th>ブースコード</th>
			<th>用途種別</th>
			<th>名称</th>
			<th>表示順</th>
			<th width="7%"></th>
		</x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- MEMO: 日付フォーマットを指定する --}}
            <td>@{{item.campus_name}}</td>
            <td>@{{item.booth_cd}}</td>
            <td>@{{item.kind_name}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.disp_order}}</td>
            <td>
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('master_mng_booth-edit', '') }}/' + item.id" />
            </td>
        </tr>

	</x-bs.table>
</x-bs.card-list>

@stop