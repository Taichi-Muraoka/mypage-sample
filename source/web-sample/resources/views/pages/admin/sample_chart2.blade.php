@extends('adminlte::page')
@section('title', '温度データ確認（モック）')

@section('content')

{{-- 検索フォーム --}}
{{-- 検索条件を保持・引き継ぐ場合は :initSearchCond=true を付ける --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="disp_mode" caption="表示切替" :select2=true :editData=$editData :select2Search=false :blank=false >
                <option value="1">月別</option>
                <option value="2">日別</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="company_id" caption="企業名" :select2=true :editData=$editData :select2Search=false :blank=false >
                <option value="1">企業１</option>
                <option value="2">企業２</option>
                <option value="3">企業３</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="店舗名" id="store_id" :select2=true :editData=$editData :select2Search=false :blank=false >
                <option value="0">仙台中央店</option>
                <option value="1">仙台北店</option>
                <option value="2">仙台東店</option>
                <option value="3">仙台西店</option>
                <option value="4">仙台南店</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="device_id" caption="機器名" :select2=true :editData=$editData :select2Search=false :blank=false >
                <option value="1">冷蔵庫１</option>
                <option value="2">冷蔵庫２</option>
                <option value="3">冷蔵庫３</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="日付選択" id="target_date" :editData=$editData />
        </x-bs.col2>
    </x-bs.row>
        {{-- hidden --}}
        <x-input.hidden id="bef_disp_mode" :editData=$editData/></x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>
    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">店舗名</th>
            <th width="20%">機器名</th>
            <th width="15%">日付</th>
            <th width="15%">時刻</th>
            <th>温度（毎時平均）</th>
        </x-slot>

        {{-- テーブル行 --}}
        {{-- モック用処理 --}}
        <tr>
            <td>仙台中央店</td>
            <td>冷蔵庫１</td>
            <td>2025/03/01</td>
            <td>0:00</td>
            <td>1.0</td>
        </tr>
        <tr>
            <td>仙台中央店</td>
            <td>冷蔵庫１</td>
            <td>2025/03/01</td>
            <td>1:00</td>
            <td>1.1</td>
        </tr>

    </x-bs.table>

<x-bs.card>
    {{-- chart --}}
    <div id="chartId" class="box">
        <div class="box-header with-border">
            <h6 class="box-title">温度グラフ表示</h6>
        </div>
        <div class="box-body">
            <div class="chart">
                <canvas id="tempChart" height="400" width="800"></canvas>
            </div>
        </div>
    </div>

</x-bs.card>
</x-bs.card-list>
@stop
