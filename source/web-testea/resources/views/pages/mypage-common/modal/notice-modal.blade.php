@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
{{-- モック用 --}}
{{-- 面談 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 1">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/06/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>面談のご案内</td>
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
        <td class="nl2br">以下の日程で面談を実施します。<br>7月1日～7月7日<br>都合の良い日をマイページからご連絡ください。</td>
    </tr>
</x-bs.table>

{{-- 特別期間講習日程連絡 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 2">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/05/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>特別期間講習日程連絡のお願い</td>
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
        <td class="nl2br">今年度の夏季特別期間講習の日程連絡のお願いです。<br>マイページより日程連絡を送信してください。</td>
    </tr>
</x-bs.table>

{{-- 成績登録 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 3">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/04/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>成績登録のお願い</td>
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
        <td class="nl2br">成績登録のお願いです。<br>マイページより成績を登録してください。</td>
    </tr>
</x-bs.table>

{{-- その他　生徒用 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 4">
    <tr>
        <th width="35%">通知日</th>
        <td>2023/01/16</td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>欠席申請受付</td>
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
        <td class="nl2br">以下の欠席申請を受け付けました。<br>授業日時：2023/01/23 16:00<br>校舎：久我山</td>
    </tr>
</x-bs.table>

{{-- その他　講師用 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 5">
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
</x-bs.table>

{{-- 本番用 --}}
{{-- <x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>

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
        <td class="nl2br"> --}}
            {{-- 本文中のURLをリンクに変換して出力する --}}
            {{-- <autolink :text="item.body"></autolink>
        </td>
    </tr>

</x-bs.table> --}}

@overwrite

{{-- モーダルの追加のボタン --}}
@section('modal-button')

{{-- モック用 --}}
{{-- 生徒のみ --}}
@can('student')
{{-- 面談日程調整へのリンク --}}
<x-button.edit vShow="item.id== 1" vueHref="'{{ route('conference') }}'" icon="" caption="面談日程連絡 " />
{{-- 特別期間講習連絡へのリンク --}}
<x-button.edit vShow="item.id== 2" vueHref="'{{ route('season_student') }}'" icon="" caption="特別期間講習連絡 " />
{{-- 生徒成績へのリンク --}}
<x-button.edit vShow="item.id== 3" vueHref="'{{ route('grades') }}'" icon="" caption="成績登録 " />
@endcan
{{-- 講師のみ --}}
@can('tutor')
{{-- 特別期間講習連絡へのリンク --}}
<x-button.edit vShow="item.id== 2" vueHref="'{{ route('season_tutor') }}'" icon="" caption="特別期間講習連絡 " />
@endcan

{{-- 本番用 --}}
{{-- 生徒のみ --}}
{{-- @can('student') --}}
{{-- 面談日程調整へのリンク --}}
{{-- <x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_5 }}"
    vueHref="'{{ route('conference') }}'" icon="" caption="面談日程連絡 " /> --}}
{{-- 特別期間講習連絡へのリンク --}}
{{-- <x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_6 }}"
    vueHref="'{{ route('season_student') }}'" icon="" caption="特別期間講習連絡 " /> --}}
{{-- 生徒成績へのリンク --}}
{{-- <x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_7 }}"
    vueHref="'{{ route('grades') }}'" icon="" caption="成績登録 " />
@endcan --}}
{{-- 講師のみ --}}
{{-- @can('tutor') --}}
{{-- 特別期間講習連絡へのリンク --}}
{{-- <x-button.edit vShow="item.type == {{ App\Consts\AppConst::CODE_MASTER_14_6 }}"
    vueHref="'{{ route('season_tutor') }}'" icon="" caption="特別期間講習連絡 " />
@endcan --}}

@overwrite