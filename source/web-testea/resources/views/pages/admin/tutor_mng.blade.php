@extends('adminlte::page')

@section('title', '教師一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="tid" caption="教師No" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="教師名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('tutor_mng-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="12%">教師No</th>
            <th width="18%">教師名</th>
            <th>メールアドレス</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.tid}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.email}}</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-detail', '') }}/' + item.tid" caption="教師情報" />
                <x-button.list-edit vueHref="'{{ route('tutor_mng-calendar', '') }}/' + item.tid" caption="カレンダー" />
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-weekly_shift', '') }}/' + item.tid" caption="空き時間" />
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-salary', '') }}/' + item.tid" caption="給与明細" />
                <x-button.list-edit href="{{ route('tutor_mng-edit', 1) }}" />
            </td>
        </tr>

    </x-bs.table>
</x-bs.card-list>

@stop