@extends('adminlte::page')

@section('title', '給与明細一覧')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 結果リスト --}}
<x-bs.card>
    <x-slot name="card_title">
        {{$teacher_name}}
    </x-slot>
    <x-bs.card-list>

        {{-- hidden 検索一覧用--}}
        <x-input.hidden id="tid" :editData=$editData />

        {{-- テーブル --}}
        <x-bs.table :button=true>

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>給与明細書</th>
                <th></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                <td>@{{item.salary_date|formatYmString}}分給与</td>
                <td>
                    <x-button.list-dtl vueHref="'{{ route('tutor_mng-detail_salary', ['tid' => $editData['tid'], '']) }}/' + item.tid" caption="給与情報" />
                </td>
            </tr>

        </x-bs.table>
    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>
</x-bs.card>

@stop