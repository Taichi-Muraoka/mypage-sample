@extends('adminlte::page')

@section('title', '特別期間講習 日程連絡一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">特別期間名</th>
            <th>校舎</th>
            <th width="15%">受付開始日</th>
            <th width="15%">ステータス</th>
            <th width="15%">連絡日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="特別期間名">2023年夏期</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
            <x-bs.td-sp caption="受付開始日">2023/06/20</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">未登録</x-bs.td-sp>
            <x-bs.td-sp caption="連絡日"></x-bs.td-sp>
            <td>
                {{-- 未登録の場合 --}}
                <div v-show="1==1"><x-button.list-edit vueHref="'{{ route('season_student-edit', '') }}/' + 1" caption="登録" /></div>
                {{-- 登録済の場合 --}}
                <div v-show="1==0"><x-button.list-dtl vueHref="'{{ route('season_student-detail', '') }}/' + 1" caption="詳細"/></div>
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="特別期間名">2023年夏期</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">西永福</x-bs.td-sp>
            <x-bs.td-sp caption="受付開始日">2023/07/01</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">未登録</x-bs.td-sp>
            <x-bs.td-sp caption="連絡日"></x-bs.td-sp>
            <td>
                {{-- 未登録の場合 --}}
                <div v-show="1==1"><x-button.list-edit vueHref="'{{ route('season_student-edit', '') }}/' + 1" caption="登録" disabled=true/></div>
                {{-- 登録済の場合 --}}
                <div v-show="1==0"><x-button.list-dtl vueHref="'{{ route('season_student-detail', '') }}/' + 1" caption="詳細"/></div>
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="特別期間名">2023年春期</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
            <x-bs.td-sp caption="受付開始日">2023/03/01</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">登録済</x-bs.td-sp>
            <x-bs.td-sp caption="連絡日">2023/03/05</x-bs.td-sp>
            <td>
                {{-- 未登録の場合 --}}
                <div v-show="1==0"><x-button.list-edit vueHref="'{{ route('season_student-edit', '') }}/' + 1" caption="登録" /></div>
                {{-- 登録済の場合 --}}
                <div v-show="1==1"><x-button.list-dtl vueHref="'{{ route('season_student-detail', '') }}/' + 1" caption="詳細"/></div>
            </td>
        </tr>
        <tr>
            <x-bs.td-sp caption="特別期間名">2022年冬期</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
            <x-bs.td-sp caption="受付開始日">2022/12/01</x-bs.td-sp>
            <x-bs.td-sp caption="ステータス">登録済</x-bs.td-sp>
            <x-bs.td-sp caption="連絡日">2022/12/05</x-bs.td-sp>
            <td>
                {{-- 未登録の場合 --}}
                <div v-show="1==0"><x-button.list-edit vueHref="'{{ route('season_student-edit', '') }}/' + 1" caption="登録" /></div>
                {{-- 登録済の場合 --}}
                <div v-show="1==1"><x-button.list-dtl vueHref="'{{ route('season_student-detail', '') }}/' + 1" caption="詳細"/></div>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop