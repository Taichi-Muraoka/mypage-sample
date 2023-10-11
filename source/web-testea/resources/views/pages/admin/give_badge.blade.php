@extends('adminlte::page')

@section('title', 'バッジ付与一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$studentList :editData=$editData
            :select2Search=true :blank=true/>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="badge_type" caption="バッジ種別" :select2=true :mastrData=$kindList :editData=$editData
            :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="認定日 From" id="authorization_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="認定日 To" id="authorization_date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">認定日</th>
            <th width="10%">バッジ種別</th>
            <th width="10%">校舎</th>
            <th width="15%">生徒名</th>
            <th width="15%">担当者名</th>
            <th>認定理由</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.authorization_date)}}</td>
            <td>@{{item.kind_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.admin_name}}</td>
            <td>@{{item.reason}}</td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル(送信確認モーダル) 出力 --}}
@include('pages.admin.modal.give_badge_output-modal', ['modal_send_confirm' => true])

@stop