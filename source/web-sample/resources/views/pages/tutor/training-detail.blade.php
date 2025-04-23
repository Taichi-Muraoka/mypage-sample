@extends('adminlte::page')

@section('title', '研修受講')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>

    <p>以下の研修を受講します。</p>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true class="fix">
        <tr>
            <th width="35%">形式</th>
            <td>{{$training->trn_type_name}}</td>
        </tr>
        <tr>
            <th>内容</th>
            <td>{{$training->text}}</td>
        </tr>
        <tr>
            <th>公開日</th>
            <td>{{$training->release_date->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>期限</th>
            <td>
                @if($training->limit_date)
                {{$training->limit_date->format('Y/m/d')}}
                @else
                無期限
                @endif
            </td>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if ($training->trn_type == App\Consts\AppConst::CODE_MASTER_12_1)
            {{-- 資料のダウンロードの場合 --}}
            <x-button.submit-href caption="ダウンロード" icon="fas fa-download"
                href="{{ Route('training-download', $editData['trn_id']) }}" />

            @elseif ($training->trn_type == App\Consts\AppConst::CODE_MASTER_12_2)
            {{-- 動画の場合 --}}
            <x-input.hidden id="trn_id" :editData=$editData />
            <x-button.submit-href caption="視聴" href="{{$training->url}}" :blank=true icon="fas fa-video"
                onClick="submitMovieBrowse" />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop