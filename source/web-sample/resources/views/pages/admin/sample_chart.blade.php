@extends('adminlte::page')
@section('title', '温度データ確認（モック）')

@section('content')

{{-- chart --}}
<x-bs.card :form=true>
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

@stop
