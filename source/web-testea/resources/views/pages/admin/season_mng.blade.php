@extends('adminlte::page')

@section('title', '特別期間講習 講習情報一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">特別期間</th>
            <th>校舎</th>
            <th>受付開始日</th>
            <th>受付終了日</th>
            <th>状態</th>
            <th>コマ組み確定日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023年夏期</td>
            <td>久我山</td>
            <td>2023/07/01</td>
            <td>2023/07/10</td>
            <td>受付前</td>
            <td></td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" />
            </td>
        </tr>
        <tr>
            <td>2023年夏期</td>
            <td>西永福</td>
            <td></td>
            <td></td>
            <td>受付前</td>
            <td></td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>
        <tr>
            <td>2023年夏期</td>
            <td>本郷</td>
            <td></td>
            <td></td>
            <td>受付前</td>
            <td></td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>
        <tr>
            <td>2023年夏期</td>
            <td>下高井戸</td>
            <td></td>
            <td></td>
            <td>受付前</td>
            <td></td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>
        <tr>
            <td>2023年春期</td>
            <td>久我山</td>
            <td>2023/03/01</td>
            <td>2023/03/10</td>
            <td>確定済</td>
            <td>2023/03/20</td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" disabled=true />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>
        <tr>
            <td>2023年春期</td>
            <td>西永福</td>
            <td>2023/03/01</td>
            <td>2023/03/10</td>
            <td>確定済</td>
            <td>2023/03/19</td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" disabled=true />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>
        <tr>
            <td>2023年春期</td>
            <td>本郷</td>
            <td>2023/03/01</td>
            <td>2023/03/10</td>
            <td>確定済</td>
            <td>2023/03/20</td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" disabled=true />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>
        <tr>
            <td>2023年春期</td>
            <td>下高井戸</td>
            <td>2023/03/01</td>
            <td>2023/03/10</td>
            <td>確定済</td>
            <td>2023/03/21</td>
            <td>
                <x-button.list-edit caption="受付期間登録" href="{{ route('season_mng-edit',1) }}" disabled=true />
                <x-button.list-dtl caption="コマ組み確定" btn="btn-primary" dataTarget="#modal-exec-confirm" disabled=true />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>
{{-- モーダル(スケジュール確定) --}}
@include('pages.admin.modal.season_mng_confirm-modal', ['modal_send_confirm' => true, 'modal_id' =>'modal-exec-confirm'])

@stop