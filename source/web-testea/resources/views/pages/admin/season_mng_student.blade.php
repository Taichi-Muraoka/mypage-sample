@extends('adminlte::page')

@section('title', '特別期間講習 生徒提出スケジュール一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="season" caption="特別期間" :select2=true>
                <option value="1">2023年春期</option>
                <option value="2">2022年冬期</option>
                <option value="3">2022年夏期</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="room" caption="校舎" :select2=true>
                <option value="1">久我山</option>
                <option value="2">春日部</option>
                <option value="3">本郷</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="status" caption="ステータス" :select2=true>
                <option value="1">未対応</option>
                <option value="2">対応中</option>
                <option value="3">対応済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">連絡日</th>
            <th>特別期間名</th>
            <th>校舎</th>
            <th>生徒名</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/05</td>
            <td>2023年春期</td>
            <td>久我山</td>
            <td>CWテスト生徒１</td>
            <td>未対応</td>
            <td>
                <x-button.list-edit vueHref="'{{ route('season_mng_student-plan', '') }}/' + 1" caption="コマ組み" />
            </td>
        </tr>
        <tr>
            <td>2023/03/04</td>
            <td>2023年春期</td>
            <td>久我山</td>
            <td>CWテスト生徒２</td>
            <td>未対応</td>
            <td>
                <x-button.list-edit vueHref="'{{ route('season_mng_student-plan', '') }}/' + 1" caption="コマ組み" />
            </td>
        </tr>
        <tr>
            <td>2023/03/04</td>
            <td>2023年春期</td>
            <td>久我山</td>
            <td>CWテスト生徒３</td>
            <td>対応済</td>
            <td>
                <x-button.list-edit vueHref="'{{ route('season_mng_student-plan', '') }}/' + 1" caption="コマ組み" />
            </td>
        </tr>


    </x-bs.table>
</x-bs.card-list>

@stop