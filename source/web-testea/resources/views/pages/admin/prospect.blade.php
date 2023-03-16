@extends('adminlte::page')

@section('title', '見込み客一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="inquiry_matter" caption="問い合わせ項目" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="希望校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="希望校舎" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="cls_cd" caption="学年" :select2=true :mastrData=$classes :editData=$editData />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('prospect-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>問い合わせ項目</th>
            <th>希望校舎</th>
            <th>学年</th>
            <th>生徒名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>無料体験授業</td>
            <td>久我山</td>
            <td>中学２年</td>
            <td>CWテスト生徒１</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('prospect-edit', 1) }}" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.prospect-modal')

@stop