@extends('adminlte::page')

@section('title', '研修閲覧状況')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="mb-3 fix">
        <tr>
            <th width="35%">形式</th>
            <td>{{$training->trn_type}}</td>
        </tr>
        <tr>
            <th>内容</th>
            <td>{{$training->text}}</td>
        </tr>
        <tr>
            <th>登録日</th>
            <td>{{$training->regist_time->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>期限</th>
            <td>
                @if($training->limit_date)
                {{$training->limit_date->format('Y/m/d')}}
                @else
                無期限
                @endif
            </td>
        </tr>
        <tr>
            <th>公開日</th>
            <td>{{$training->release_date->format('Y/m/d')}}</td>
        </tr>
    </x-bs.table>

    {{-- 結果リスト --}}
    <x-bs.card-list>

        {{-- 検索時にIDを送信 --}}
        <x-input.hidden id="trn_id" :editData=$training />

        {{-- テーブル --}}
        <x-bs.table>

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>講師名</th>
                <th>閲覧日時</th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr v-for="item in paginator.data" v-cloak>
                <td>@{{item.name}}</td>
                <td>@{{$filters.formatYmdHm(item.browse_time)}}</td>
            </tr>

        </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>

</x-bs.card>

@stop
