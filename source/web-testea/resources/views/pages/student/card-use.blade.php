@extends('adminlte::page')

@section('title', 'ギフトカード使用申請')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のギフトカードについて使用申請を行います。</p>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">付与日</th>
            <td>{{$editData['grant_time']->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>ギフトカード名</th>
            <td>{{$editData['card_name']}}</td>
        </tr>
        <tr>
            <th>割引内容</th>
            <td>{{$editData['discount']}}</td>
        </tr>
        <tr>
            <th>使用期間 開始日</th>
            <td>{{$editData['term_start']->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>使用期間 終了日</th>
            <td>{{$editData['term_end']->format('Y/m/d')}}</td>
        </tr>
    </x-bs.table>

    {{-- hidden --}}
    <x-input.hidden id="card_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-edit caption="送信" />
        </div>
    </x-slot>

</x-bs.card>

@stop