@extends('adminlte::page')

@section('title', '追加請求申請一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('surcharge-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>請求種別</th>
            <th>時間（分）</th>
            <th>金額</th>
            <th>ステータス</th>
            <th>支払年月</th>
            <th>支払状況</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10</td>
            <td>業務依頼（本部）</td>
            <td>60</td>
            <td>1000</td>
            <td>承認</td>
            <td>2023/03</td>
            <td>未処理</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '1']"/>
                <x-button.list-edit disabled/>
            </td>
        </tr>
        <tr>
            <td>2023/01/09</td>
            <td>経費</td>
            <td></td>
            <td>2000</td>
            <td>差戻し</td>
            <td></td>
            <td></td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '2']"/>
                <x-button.list-edit href="{{ route('surcharge-edit', 1) }}" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.tutor.modal.surcharge-modal')

@stop