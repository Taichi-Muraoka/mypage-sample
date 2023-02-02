@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>

    <tr>
        <th width="35%">報告日</th>
        <td>@{{item.registTime|formatYmd}}</td>
    </tr>
    <tr>
        <th>教師名</th>
        <td>@{{item.name}}</td>
    </tr>
    <tr>
        <th>実施月</th>
        <td>@{{item.reportDate|formatYm}}</td>
    </tr>
    <tr>
        <th>教室</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>登録済みの授業報告一覧</th>
        <td>
            <x-bs.table>
                <x-slot name="thead">
        <th width="35%">授業日時</th>
        <th>生徒名</th>
        <th width="25%">授業時間数</th>
        </x-slot>

        {{--モック用データ--}}
    <tr v-for="item in item.class" v-cloak>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
        <td>@{{item.name}}</td>
        <td>@{{item.r_minutes}}</td>
    </tr>
</x-bs.table>
</td>
</tr>
<tr>
    <th>実施回数表示</th>
    <td>
        {{-- テーブル --}}
        <x-bs.table>

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
    <th>生徒名</th>
    <th width="15%">回数</th>
    </x-slot>

    {{-- テーブル行 --}}
<tr v-for="item in item.student" v-cloak>
    <x-bs.td-sp>@{{item.name}}</x-bs.td-sp>
    <x-bs.td-sp class="not-center">@{{item.name_count}}回</x-bs.td-sp>
</tr>

</x-bs.table>
</td>
</tr>
<tr>
    <th>上記以外に実施した授業や事務時間</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.officeWork}}</td>
</tr>
<tr>
    <th>その他特記事項（テキストの購入・イレギュラーな交通費変更等）</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br">@{{item.other}}</td>
</tr>

</x-bs.table>

@overwrite