@extends('adminlte::page')

@section('title', '要振替授業一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=false/>
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false/>
            @endcan
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$studentList :editData=$editData :select2Search=false/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師名" :select2=true :mastrData=$tutorList :editData=$editData :select2Search=false/>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>校舎</th>
            <th>授業日・時限</th>
            <th>コース</th>
            <th>生徒名</th>
            <th>講師名</th>
            <th>教科</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>久我山</td>
            <td>2023/01/30 4限</td>
            <td>個別指導コース</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>英語</td>
            <td>振替中</td>
            <td>
                <x-button.list-edit href="{{ route('transfer_check-new') }}" icon="" caption="振替情報登録" />
            </td>
        </tr>
        <tr>
            <td>久我山</td>
            <td>2023/01/31 6限</td>
            <td>個別指導コース</td>
            <td>CWテスト生徒２</td>
            <td>CWテスト教師１０２</td>
            <td>数学</td>
            <td>未振替</td>
            <td>
                <x-button.list-edit href="{{ route('transfer_check-new') }}" icon="" caption="振替情報登録" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
{{-- @include('pages.admin.modal.transfer_required-modal') --}}

@stop