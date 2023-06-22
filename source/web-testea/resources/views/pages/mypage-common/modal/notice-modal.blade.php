@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>

    <tr>
        <th width="35%">通知日</th>
        <td>@{{item.date|formatYmd}}</td>
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

</x-bs.table>

@overwrite

{{-- モーダルの追加のボタン --}}
@section('modal-button')

{{-- 生徒のみ --}}
@can('student')
{{-- 面談日程調整へのリンク --}}
<x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_5 }}"
    vueHref="'{{ route('conference') }}'" icon="" caption="面談日程連絡 " />
{{-- 特別期間講習連絡へのリンク --}}
<x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_6 }}"
    vueHref="'{{ route('season_student') }}'" icon="" caption="特別期間講習連絡 " />
{{-- 生徒成績へのリンク --}}
<x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_7 }}"
    vueHref="'{{ route('grades') }}'" icon="" caption="成績登録 " />
@endcan
{{-- 講師のみ --}}
@can('tutor')
{{-- 特別期間講習連絡へのリンク --}}
<x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_6 }}"
    vueHref="'{{ route('season_tutor') }}'" icon="" caption="特別期間講習連絡 " />
@endcan

@overwrite