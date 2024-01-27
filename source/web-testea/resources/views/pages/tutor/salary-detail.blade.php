@extends('adminlte::page')

@section('title', '給与明細書表示')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>
    <p>{{$salary->tutor_name}} 様</p>

    <p>{{$salary->salary_date->format('Y年n月')}} 労働分</p>
    <p>{{$salary_import->payment_date->format('Y年n月j日')}} 支給</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4" :smartPhone=true>
        <tr>
            <th width="35%">支払金額</th>
            <x-bs.td-sp class="t-price">{{number_format($salary->total_amount)}}円</x-bs.td-sp>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    @if(count($salary_detail_1) > 0)
        <x-bs.form-title>源泉計算対象</x-bs.form-title>
        {{-- テーブル --}}
        <x-bs.table :smartPhone=true>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>費目</th>
                <th width="15%">単価</th>
                <th width="15%">時間(h)</th>
                <th width="15%">金額</th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 0; $i < count($salary_detail_1); $i++)
                {{-- 源泉計算用小計の金額は小計に表示する --}}
                @if($salary_detail_1[$i]->item_name != config('appconf.subtotal_withholding'))
                    <tr>
                        <x-bs.td-sp caption="費目">{{$salary_detail_1[$i]->item_name}}</x-bs.td-sp>
                        <x-bs.td-sp caption="単価" class="t-price">
                            @if($salary_detail_1[$i]->hour_payment != null)
                            {{number_format($salary_detail_1[$i]->hour_payment)}}円
                            @endif
                        </x-bs.td-sp>
                        <x-bs.td-sp caption="時間(h)" class="t-price">
                            @if($salary_detail_1[$i]->hour != null)
                            {{floatval($salary_detail_1[$i]->hour)}}
                            @endif
                        </x-bs.td-sp>
                        <x-bs.td-sp caption="金額" class="t-price">{{number_format($salary_detail_1[$i]->amount)}}円</x-bs.td-sp>
                    </tr>
                @else
                    <tr>
                        <x-bs.td-sp class="font-weight-bold">小計</x-bs.td-sp>
                        <x-bs.td-sp class="font-weight-bold t-price" colspan="3">{{number_format($salary_detail_1[$i]->amount)}}円</x-bs.td-sp>
                    </tr>
                @endif
            @endfor
        </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-3"></div>

    @if(count($salary_detail_2) > 0)
        <x-bs.form-title>源泉計算対象外</x-bs.form-title>
        {{-- テーブル --}}
        <x-bs.table :smartPhone=true>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>費目</th>
                <th width="15%">金額</th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 0; $i < count($salary_detail_2); $i++)
                <tr>
                    <x-bs.td-sp caption="費目">{{$salary_detail_2[$i]->item_name}}</x-bs.td-sp>
                    <x-bs.td-sp caption="金額" class="t-price">{{number_format($salary_detail_2[$i]->amount)}}円</x-bs.td-sp>
                </tr>
            @endfor
                <tr>
                    <x-bs.td-sp class="font-weight-bold">小計</x-bs.td-sp>
                    <x-bs.td-sp class="font-weight-bold t-price">{{number_format($salary_detail_2_subtotal)}}円</x-bs.td-sp>
                </tr>
        </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-3"></div>

    @if(count($salary_detail_3) > 0)
        <x-bs.form-title>控除</x-bs.form-title>
        {{-- テーブル --}}
        <x-bs.table :smartPhone=true>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>費目</th>
                <th width="15%">金額</th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 0; $i < count($salary_detail_3); $i++)
                <tr>
                    <x-bs.td-sp caption="費目">{{$salary_detail_3[$i]->item_name}}</x-bs.td-sp>
                    <x-bs.td-sp caption="金額" class="t-price">{{number_format($salary_detail_3[$i]->amount)}}円</x-bs.td-sp>
                </tr>
            @endfor
                <tr>
                    <x-bs.td-sp class="font-weight-bold">小計</x-bs.td-sp>
                    <x-bs.td-sp class="font-weight-bold t-price">{{number_format($salary_detail_3_subtotal)}}円</x-bs.td-sp>
                </tr>
        </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-5"></div>

    <x-bs.table :hover=false :vHeader=true class="mb-4" :smartPhone=true>
        <tr>
            <th width="35%">備考</th>
            <x-bs.td-sp>{!! nl2br(e($salary->memo)) !!}</x-bs.td-sp>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <div class="d-flex justify-content-end">
                <x-button.submit-href caption="PDFダウンロード" icon="fas fa-download"
                    href="{{ Route('salary-pdf', $editData['date']) }}" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop