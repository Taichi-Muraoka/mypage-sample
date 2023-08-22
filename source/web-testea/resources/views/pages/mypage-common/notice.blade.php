@extends('adminlte::page')

@section('title', 'お知らせ一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">通知日</th>
            <th>タイトル</th>
            <th>送信元校舎</th>
            <th width="20%">送信者</th>
            <th></th>
        </x-slot>

    @can('student')
        <tr>
            <td>2023/06/16</td>
            <td>面談のご案内</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '1']"/>
            </td>
        </tr>
        <tr>
            <td>2023/05/16</td>
            <td>特別期間講習日程連絡のお願い</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '2']"/>
            </td>
        </tr>
        <tr>
            <td>2023/04/16</td>
            <td>成績登録のお願い</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '3']"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/16</td>
            <td>欠席申請受付</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '4']"/>
            </td>
        </tr>
        <tr>
            <td>2023/1/16</td>
            <td>月謝のお知らせ</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '5']"/>
            </td>
        </tr>
    @endcan

    @can('tutor')
        <tr>
            <td>2023/05/16</td>
            <td>特別期間講習日程連絡のお願い</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '2']"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/16</td>
            <td>生徒欠席連絡</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '6']"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/16</td>
            <td>給与のお知らせ</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '7']"/>
            </td>
        </tr>
        <tr>
            <td>2023/01/16</td>
            <td>追加請求申請に関するお知らせ</td>
            <td>本部</td>
            <td>本部管理者</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => '8']"/>
            </td>
        </tr>
    @endcan
        {{-- 本番用 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <x-bs.td-sp caption="通知日" class="t-minimum">@{{item.date|formatYmd}}</x-bs.td-sp>
            <x-bs.td-sp caption="タイトル">@{{item.title}}</x-bs.td-sp>
            <x-bs.td-sp caption="送信元教室">@{{item.room_name}}</x-bs.td-sp>
            <x-bs.td-sp caption="送信者">@{{item.sender}}</x-bs.td-sp>
            <td>
                <x-button.list-dtl :vueDataAttr="['target' => '\'#modal-dtl\'', 'id' => 'item.id']" />
            </td>
        </tr> --}}

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
{{-- 模試・イベント申込 --}}
{{-- @include('pages.mypage-common.modal.notice_event-modal', ['modal_id' => 'modal-dtl-event']) --}}
{{-- 短期個別講習案内 --}}
{{-- @include('pages.mypage-common.modal.notice_course-modal', ['modal_id' => 'modal-dtl-course']) --}}
{{-- それ以外 --}}
{{-- @include('pages.mypage-common.modal.notice_absent-modal', ['modal_id' => 'modal-dtl-absent']) --}}
{{-- 共通のモーダルとした --}}
@include('pages.mypage-common.modal.notice-modal')

@stop