@extends('adminlte::page')

@section('title', 'サンプル一覧（モック）')

@section('content')

{{-- 検索フォーム --}}
{{-- 検索条件を保持・引き継ぐ場合は :initSearchCond=true を付ける --}}
<x-bs.card :search=true :initSearchCond=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- 生徒リスト 検索Boxを表示する (select2Search=true) --}}
            <x-input.select id="student_id" caption="生徒名" :select2=true :editData=$editData :select2Search=true :blank=true >
                <option value="1">テスト生徒１</option>
                <option value="2">テスト生徒２</option>
                <option value="3">テスト生徒３</option>
                <option value="4">テスト生徒４</option>
                <option value="5">テスト生徒５</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            {{-- ステータスや種別は、検索Boxを非表示とする (select2Search=false) --}}
            <x-input.select caption="ステータス" id="sample_state" :select2=true :editData=$editData :select2Search=false :blank=true >
                <option value="0">未対応</option>
                <option value="1">対応済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            {{-- 文字列検索（部分一致）--}}
            <x-input.text caption="サンプル件名" id="sample_title" :rules=$rules :editData=$editData />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('samplemock_mng-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">登録日</th>
            <th width="15%">生徒名</th>
            <th>サンプル件名</th>
            <th width="15%">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        {{-- モック用処理 --}}
        <tr>
            <td>2025/03/01</td>
            <td>テスト生徒１</td>
            <td>テスト件名１</td>
            <td>未対応</td>
            <td>
                {{-- 詳細モーダル --}}
                <x-button.list-dtl />
                {{-- 編集画面 --}}
                <x-button.list-edit href="{{ route('samplemock_mng-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2025/03/01</td>
            <td>テスト生徒２</td>
            <td>テスト件名２</td>
            <td>対応済</td>
            <td>
                {{-- 詳細モーダル --}}
                <x-button.list-dtl />
                {{-- 編集画面 --}}
                <x-button.list-edit href="{{ route('samplemock_mng-edit', 1) }}" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.samplemock_mng-modal')

@stop
