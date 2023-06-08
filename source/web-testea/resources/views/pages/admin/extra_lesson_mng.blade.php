@extends('adminlte::page')

@section('title', '追加授業依頼一覧')

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
            {{-- <x-input.select id="changes_state" caption="ステータス" :select2=true :mastrData=$statusList /> --}}
            <x-input.select caption="ステータス" id="state" :select2=true :editData=$editData>
                <option value="1">未対応</option>
                <option value="2">対応済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('extra_lesson_mng-new') }}" :small=true caption='追加授業登録'/>
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">依頼日</th>
            <th>校舎</th>
            <th width="20%">生徒名</th>
            <th width="15%">ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/02/20</td>
            <td>久我山</td>
            <td>CWテスト生徒１</td>
            <td>未対応</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('extra_lesson_mng-edit',1) }}" />
            </td>
        </tr>


    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.extra_lesson_mng-modal')
{{-- モーダル(送信確認モーダル) 受付 --}}
{{--@include('pages.admin.modal.extra_lesson_mng_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance']) --}}

@stop