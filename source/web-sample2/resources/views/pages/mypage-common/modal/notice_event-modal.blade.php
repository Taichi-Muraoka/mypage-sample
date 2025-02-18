@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>

    <tr>
        <th width="35%">通知日</th>
        <td>@{{$filters.formatYmd(item.date)}}</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>@{{item.title}}</td>
    </tr>
    <tr>
        <th>送信元教室</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>送信者名</th>
        <td>@{{item.sender}}</td>
    </tr>
    <tr>
        <th>内容</th>
        <td class="nl2br">
            {{-- 本文中のURLをリンクに変換して出力する --}}
            <autolink :text="item.body"></autolink>
        </td>
    </tr>
    <tr>
        <th>模試・イベント情報</th>
        <td>@{{item.tmid_event_name}}（@{{$filters.formatYmd(item.tmid_event_date)}}）</td>
    </tr>

</x-bs.table>

@overwrite

{{-- モーダルの追加のボタン --}}
@section('modal-button')

{{-- 生徒のみ --}}
@can('student')
{{-- 模試・イベント申込へ --}}
<x-button.edit vueHref="'{{ route('event') }}/' + item.type + '/' + item.id" icon="" caption="模試・イベント申込" />
@endcan

@overwrite