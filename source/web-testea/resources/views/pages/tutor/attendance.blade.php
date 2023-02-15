@extends('adminlte::page')

@section('title', '本日の授業実施登録')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>授業日・時限</th>
            <th>教室</th>
            <th>授業スペース</th>
            <th>生徒名</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/02/13 5限</td>
            <td>久我山</td>
            <td>第一校舎１テーブル</td>
            <td>CWテスト生徒１</td>
            <td>出席</td>
            <td>
                {{-- モーダルを開く詳細ボタンを使用する --}}
                {{-- 申請者種別が教師のため更新ボタン非活性 --}}
                <x-button.list-dtl caption="授業実施" btn="btn-primary" dataTarget="#modal-dtl-attendance"
                    :vueDataAttr="['schedule_id' => '1']"  disabled="true" />
            </td>
        </tr>
        <tr>
            <td>2023/02/13 6限</td>
            <td>久我山</td>
            <td>第一校舎３テーブル</td>
            <td>CWテスト生徒２</td>
            <td></td>
            <td>
                {{-- モーダルを開く詳細ボタンを使用する --}}
                <x-button.list-dtl caption="授業実施" btn="btn-primary" dataTarget="#modal-dtl-attendance"
                    :vueDataAttr="['schedule_id' => '1']"  />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル(授業実施モーダル) --}}
@include('pages.tutor.modal.attendance_exec-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-attendance'])

@stop