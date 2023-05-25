@extends('adminlte::page')

@section('title', '授業報告書一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- @can('roomAdmin') --}}
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            {{-- <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan --}}
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
            {{-- <x-input.select id="cls_cd" caption="学年" :select2=true :editData=$editData :mastrData=$classes /> --}}
            <x-input.select id="cls_cd" caption="学年" :select2=true >
                <option value="1">高3</option>
                <option value="2">高2</option>
                <option value="3">高1</option>
                <option value="4">中3</option>
                <option value="5">中2</option>
                <option value="6">中1</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="sname" caption="生徒名" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="tname" caption="講師名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="コース" id="course" :select2=true :editData=$editData>
                <option value="1">個別指導</option>
                <option value="2">集団授業</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="承認ステータス" id="status" :select2=true :editData=$editData>
                <option value="1">承認待ち</option>
                <option value="2">承認</option>
                <option value="3">差戻し</option>
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
            <th class="t-minimum">登録日</th>
            <th>講師名</th>
            <th>授業日・時限</th>
            <th>校舎</th>
            <th>コース</th>
            <th>生徒名</th>
            <th>承認ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="登録日">2023/05/15</x-bs.td-sp>
            <x-bs.td-sp caption="講師名">CWテスト教師１０１</x-bs.td-sp>
            <x-bs.td-sp caption="授業日・時限">2023/05/15 4限</x-bs.td-sp>
            <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
            <x-bs.td-sp caption="コース">個別指導</x-bs.td-sp>
            <x-bs.td-sp caption="生徒名">CWテスト生徒１</x-bs.td-sp>
            <x-bs.td-sp caption="承認ステータス">承認待ち</x-bs.td-sp>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('report_check-edit', '1') }}" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.report_check-modal')

@stop