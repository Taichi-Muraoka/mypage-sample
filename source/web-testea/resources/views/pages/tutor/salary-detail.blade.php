@extends('adminlte::page')

@section('title', '給与明細書表示')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>{{$salary->salary_date->format('Y年m月')}}度給与（{{$salary->prev_month->format('Y年m月')}}分）</p>
    <p>株式会社 コー・ワークス</p>
    <p>{{$salary->teacher_name}} 様</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">税額表</th>
            <td>{{$salary->tax_table}}</td>
        </tr>
        <tr>
            <th>扶養人数</th>
            <td>{{$salary->dependents}}</td>
        </tr>
    </x-bs.table>

    @if(count($salary_detail_1) > 0)
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>支給</x-bs.form-title>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($salary_detail_1); $i++) <tr>
            <th>{{$salary_detail_1[$i]->item_name}}</th>
            <td class="t-price">{{number_format($salary_detail_1[$i]->amount)}}</td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

    @if(count($salary_detail_2) > 0)
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>控除</x-bs.form-title>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($salary_detail_2); $i++) <tr>
            <th>{{$salary_detail_2[$i]->item_name}}</th>
            <td class="t-price">{{number_format($salary_detail_2[$i]->amount)}}</td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

    @if(count($salary_detail_3) > 0)
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>その他</x-bs.form-title>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($salary_detail_3); $i++) <tr>
            <th>{{$salary_detail_3[$i]->item_name}}</th>
            <td class="t-price">{{number_format($salary_detail_3[$i]->amount)}}</td>
            </tr>
            @endfor
    </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>合計</x-bs.form-title>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($salary_detail_4); $i++) <tr>
            <th>{{$salary_detail_4[$i]->item_name}}</th>
            <td class="t-price">{{number_format($salary_detail_4[$i]->amount)}}</td>
            </tr>
            @endfor
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