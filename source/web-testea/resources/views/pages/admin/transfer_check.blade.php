@extends('adminlte::page')

@section('title', '振替授業調整一覧')

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
            <x-input.select id="approval_state" caption="ステータス" :select2=false >
                <option value="0">管理者承認待ち</option>
                <option value="1">承認待ち</option>
                <option value="2">承認</option>
                <option value="3">差戻し</option>
                <option value="4">管理者対応済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="生徒名" id="student_name" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text caption="講師名" id="teacher_name" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_check-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>申請者種別</th>
            <th>校舎</th>
            <th>授業日・時限</th>
            <th>生徒名</th>
            <th>講師名</th>
            <th>当月依頼回数</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/16</td>
            <td>生徒</td>
            <td>久我山</td>
            <td>2023/01/30 4限</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>1</td>
            <td>承認</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_check-edit', 1) }}"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/17</td>
            <td>講師</td>
            <td>久我山</td>
            <td>2023/01/31 4限</td>
            <td>CWテスト生徒１</td>
            <td>CWテスト教師１０１</td>
            <td>1</td>
            <td>承認待ち</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_check-edit', 1) }}"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/08</td>
            <td>講師</td>
            <td>久我山</td>
            <td>2023/01/15 6限</td>
            <td>CWテスト生徒２</td>
            <td>CWテスト教師１０１</td>
            <td>2</td>
            <td>管理者承認待ち</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('transfer_check-edit', 1) }}"/>
                <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-approval" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.transfer_check-modal')
{{-- モーダル(送信確認モーダル) 承認 --}}
@include('pages.admin.modal.transfer_check_approval-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-approval'])

@stop