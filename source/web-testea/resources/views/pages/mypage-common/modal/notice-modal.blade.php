@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
{{-- モック用 --}}
{{-- その他　講師用 --}}
{{-- <x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 6">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/01/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>生徒欠席連絡</td>
    </tr>
    <tr>
        <th>送信元校舎</th>
        <td>本部</td>
    </tr>
    <tr>
        <th>送信者名</th>
        <td>本部管理者</td>
    </tr>
    <tr>
        <th>内容</th>
        <td class="nl2br">CWテスト生徒１さんより、授業欠席の連絡がありました。<br>授業日時：2023/01/23 16:00<br>校舎：久我山</td>
    </tr>
</x-bs.table> --}}

{{-- 給与 --}}
{{-- <x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 7">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/01/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>給与のお知らせ</td>
    </tr>
    <tr>
        <th>送信元校舎</th>
        <td>本部</td>
    </tr>
    <tr>
        <th>送信者名</th>
        <td>本部管理者</td>
    </tr>
    <tr>
        <th>内容</th>
        <td class="nl2br">給与のお知らせです。</td>
    </tr>
</x-bs.table> --}}

{{-- 追加請求 --}}
{{-- <x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 8">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/01/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>追加請求申請に関するお知らせ</td>
    </tr>
    <tr>
        <th>送信元校舎</th>
        <td>本部</td>
    </tr>
    <tr>
        <th>送信者名</th>
        <td>本部管理者</td>
    </tr>
    <tr>
        <th>内容</th>
        <td class="nl2br">追加請求申請に関するお知らせです。</td>
    </tr>
</x-bs.table> --}}

{{-- 本番用 --}}
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

</x-bs.table>

@overwrite

{{-- モーダルの追加のボタン --}}
@section('modal-button')

{{-- モック用 --}}
{{-- 講師のみ --}}
{{-- @can('tutor') --}}
{{-- 特別期間講習連絡へのリンク --}}
{{-- <x-button.edit vShow="item.id== 2" vueHref="'{{ route('season_tutor') }}'" icon="" caption="特別期間講習連絡 " /> --}}
{{-- <x-button.edit vShow="item.id== 7" vueHref="'{{ route('salary') }}'" icon="" caption="給与明細 " /> --}}
{{-- <x-button.edit vShow="item.id== 8" vueHref="'{{ route('surcharge') }}'" icon="" caption="追加請求申請 " /> --}}
{{-- @endcan --}}

{{-- 本番用 --}}
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
{{-- 請求情報へのリンク --}}
<x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_8 }}"
    vueHref="'{{ route('invoice') }}'" icon="" caption="請求情報 " />
@endcan
{{-- 講師のみ --}}
@can('tutor')
{{-- 特別期間講習連絡へのリンク --}}
<x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_6 }}"
    vueHref="'{{ route('season_tutor') }}'" icon="" caption="特別期間講習連絡 " />
@endcan

@overwrite