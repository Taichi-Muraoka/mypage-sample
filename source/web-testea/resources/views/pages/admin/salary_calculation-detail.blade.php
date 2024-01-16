@extends('adminlte::page')

@section('title', '給与情報集計・データ出力')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="mb-3 fix">
        <tr>
            <th width="35%">対象年月</th>
            <td>{{$salary_mng->salary_date->format('Y年m月')}}</td>
        </tr>
        <tr>
            <th>確定日</th>
            <td>
                <span v-if="{{$salary_mng->confirm_date}} != null">
                    {{$salary_mng->confirm_date}}
                </span>
            </td>
        </tr>
        <tr>
            <th>状態</th>
            <td>{{$salary_mng->state_name}}</td>
        </tr>
    </x-bs.table>

    <div class="d-flex justify-content-end">
        <x-button.submit-exec caption="集計実行" dataTarget="#modal-dtl-calc" :dataAttr="['id' => $editData['salaryDate']]"/>
        <x-button.submit-exec caption="確定処理" dataTarget="#modal-dtl-confirm" />
    </div>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 結果リスト --}}
    <x-bs.card-list>

        {{-- カードヘッダ右 --}}
        <x-slot name="tools">
            <x-button.submit-href caption="更新" icon="fas fa-sync" :small=true btn="default" onClickPrevent="search" />
        </x-slot>

        {{-- 検索時にIDを送信 --}}
        <x-input.hidden id="event_id" />

        {{-- テーブル --}}
        <x-bs.table :button="true">

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th width="10%">講師ID</th>
                <th width="10%">講師名</th>
                <th width="5%">ベース給</th>
                <th width="5%">個別</th>
                <th width="5%">１対２</th>
                <th width="5%">１対３</th>
                <th width="5%">集団</th>
                <th width="5%">家庭教師</th>
                <th width="5%">演習</th>
                <th width="5%">ハイプラン</th>
                <th width="5%">事務作業</th>
                <th width="5%">経費(対象)</th>
                <th width="5%">経費(対象外)</th>
                <th width="5%">交通費1</th>
                <th width="5%">交通費2</th>
                <th width="5%">交通費3</th>
                <th width="5%"></th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr>
                <td>101</td>
                <td>CWテスト講師１０１</td>
                <td class="t-price">1,300</td>
                <td class="t-price">18</td>
                <td class="t-price">3</td>
                <td class="t-price">4.5</td>
                <td class="t-price">3</td>
                <td class="t-price">6</td>
                <td class="t-price">2</td>
                <td class="t-price">10</td>
                <td class="t-price">2</td>
                <td class="t-price">1,500</td>
                <td class="t-price">800</td>
                <td class="t-price">4,000</td>
                <td class="t-price">1,200</td>
                <td class="t-price">0</td>
                <td>
                    {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                    <x-button.list-dtl/>
                </td>
            </tr>

        </x-bs.table>

    </x-bs.card-list>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            <div class="d-flex justify-content-end">
                <x-button.submit-exec caption="データ出力" dataTarget="#modal-dtl-output" icon="fas fa-download" />
            </div>
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル(詳細) --}}
@include('pages.admin.modal.salary_calculation-detail-modal')
{{-- モーダル(集計実行) --}}
@include('pages.admin.modal.salary_calculation-calc-modal', 
    ['modal_send_confirm' => true, 'modal_id' =>'modal-dtl-calc'])
{{-- モーダル(経費確定) --}}
@include('pages.admin.modal.salary_calculation-confirm-modal', 
    ['modal_send_confirm' => true, 'modal_id' =>'modal-dtl-confirm'])
{{-- モーダル(CSV出力実行) --}}
@include('pages.admin.modal.salary_calculation-output-modal', 
    ['modal_send_confirm' => true, 'modal_id' =>'modal-dtl-output'])

@stop