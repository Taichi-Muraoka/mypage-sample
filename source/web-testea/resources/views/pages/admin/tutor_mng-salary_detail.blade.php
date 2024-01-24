@extends('adminlte::page')

@section('title', '給与明細書表示')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 三階層目の場合：親ページを指定(URLとタイトル) --}}
@section('parent_page', route('tutor_mng-salary', $editData['tid']))

@section('parent_page_title', '給与明細一覧')

@section('content')

<x-bs.card :form=true>

    <p>{{$salary->tutor_name}} 様</p>

    <p>{{$salary->salary_date->format('Y年n月')}} 労働分</p>
    <p>2023年8月16日 支給</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">支払金額</th>
            <td class="t-price">{{number_format($salary->total_amount)}}円</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    @if(count($salary_detail_1) > 0)
        <x-bs.form-title>源泉計算対象</x-bs.form-title>
        {{-- テーブル --}}
        <x-bs.table>
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
                        <td>{{$salary_detail_1[$i]->item_name}}</td>
                        <td class="t-price">
                            @if($salary_detail_1[$i]->hour_payment != null)
                            {{number_format($salary_detail_1[$i]->hour_payment)}}円
                            @endif
                        </td>
                        <td class="t-price">
                            @if($salary_detail_1[$i]->hour != null)
                            {{floatval($salary_detail_1[$i]->hour)}}
                            @endif
                        </td>
                        <td class="t-price">{{number_format($salary_detail_1[$i]->amount)}}円</td>
                    </tr>
                @else
                    <tr>
                        <td class="font-weight-bold">小計</td>
                        <td class="font-weight-bold t-price" colspan="3">{{number_format($salary_detail_1[$i]->amount)}}円</td>
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
        <x-bs.table>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>費目</th>
                <th width="15%">金額</th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 0; $i < count($salary_detail_2); $i++)
                <tr>
                    <td>{{$salary_detail_2[$i]->item_name}}</td>
                    <td class="t-price">{{number_format($salary_detail_2[$i]->amount)}}円</td>
                </tr>
            @endfor
                <tr>
                    <td class="font-weight-bold">小計</td>
                    <td class="font-weight-bold t-price" colspan="3">{{number_format($salary_detail_2_subtotal)}}円</td>
                </tr>
        </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-3"></div>

    @if(count($salary_detail_3) > 0)
        <x-bs.form-title>控除</x-bs.form-title>
        {{-- テーブル --}}
        <x-bs.table>
            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th>費目</th>
                <th width="15%">金額</th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 0; $i < count($salary_detail_3); $i++)
                <tr>
                    <td>{{$salary_detail_3[$i]->item_name}}</td>
                    <td class="t-price">{{number_format($salary_detail_3[$i]->amount)}}円</td>
                </tr>
            @endfor
                <tr>
                    <td class="font-weight-bold">小計</td>
                    <td class="font-weight-bold t-price" colspan="3">{{number_format($salary_detail_3_subtotal)}}円</td>
                </tr>
        </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-5"></div>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">備考</th>
            <td>{!! nl2br(e($salary->memo)) !!}</td>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back url="{{ Route('tutor_mng-salary', $editData['tid']) }}" />
            <div class="d-flex justify-content-end">
                <x-button.submit-href caption="PDFダウンロード" icon="fas fa-download"
                    href="{{ Route('tutor_mng-pdf_salary', ['tid' => $editData['tid'], 'date' => $editData['date']]) }}" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop