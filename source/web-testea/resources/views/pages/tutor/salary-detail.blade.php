@extends('adminlte::page')

@section('title', '給与明細書表示')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>{{$salary->teacher_name}} 様</p>

    <p>2023年7月 労働分</p>
    <p>2023年8月16日 支給</p>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">支払金額</th>
            <td class="t-price">89,072円</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

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
        <tr>
            <td>授業給（個別）</td>
            <td class="t-price">1,600円</td>
            <td class="t-price">28.5</td>
            <td class="t-price">67,200円</td>
        </tr>
        <tr>
            <td>授業給（家庭教師）</td>
            <td class="t-price">3,500円</td>
            <td class="t-price">3</td>
            <td class="t-price">10,500円</td>
        </tr>
        <tr>
            <td>事務作業給</td>
            <td class="t-price">988円</td>
            <td class="t-price">2</td>
            <td class="t-price">1,976円</td>
        </tr>
        <tr>
            <td>特別報酬</td>
            <td class="t-price"></td>
            <td class="t-price"></td>
            <td class="t-price">1,800円</td>
        </tr>
        <tr>
            <td>ペナルティ</td>
            <td class="t-price"></td>
            <td class="t-price"></td>
            <td class="t-price">-800円</td>
        </tr>
        <tr>
            <td class="font-weight-bold">小計</td>
            <td></td>
            <td></td>
            <td class="font-weight-bold t-price">80,676円</td>
        </tr>

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>源泉計算対象外</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>費目</th>
            <th width="15%"></th>
            <th width="15%"></th>
            <th width="15%">金額</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>交通費</td>
            <td class="t-price"></td>
            <td class="t-price"></td>
            <td class="t-price">4,396円</td>
        </tr>
        <tr>
            <td>経費</td>
            <td class="t-price"></td>
            <td class="t-price"></td>
            <td class="t-price">4,000円</td>
        </tr>
        <tr>
            <td class="font-weight-bold">小計</td>
            <td></td>
            <td></td>
            <td class="font-weight-bold t-price">8,396円</td>
        </tr>

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-5"></div>

    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="35%">備考</th>
            <td>特別報酬：1200円×1.5時間 / 遅刻：07/13 15分</td>
        </tr>
    </x-bs.table>

{{--     @if(count($salary_detail_1) > 0) --}}
    {{-- 余白 --}}
{{--     <div class="mb-3"></div> --}}

{{--     <x-bs.form-title>支給</x-bs.form-title> --}}

{{--     <x-bs.table :hover=false :vHeader=true class="mb-4"> --}}
        {{-- テーブル行 --}}
{{--         @for ($i = 0; $i < count($salary_detail_1); $i++) <tr> --}}
{{--             <th>{{$salary_detail_1[$i]->item_name}}</th> --}}
{{--             <td class="t-price">{{number_format($salary_detail_1[$i]->amount)}}</td> --}}
{{--             </tr> --}}
{{--             @endfor --}}
{{--     </x-bs.table> --}}
{{--     @endif --}}

{{--     @if(count($salary_detail_2) > 0) --}}
    {{-- 余白 --}}
{{--     <div class="mb-3"></div> --}}

{{--     <x-bs.form-title>控除</x-bs.form-title> --}}

{{--     <x-bs.table :hover=false :vHeader=true class="mb-4"> --}}
        {{-- テーブル行 --}}
{{--         @for ($i = 0; $i < count($salary_detail_2); $i++) <tr> --}}
{{--             <th>{{$salary_detail_2[$i]->item_name}}</th> --}}
{{--             <td class="t-price">{{number_format($salary_detail_2[$i]->amount)}}</td> --}}
{{--             </tr> --}}
{{--             @endfor --}}
{{--     </x-bs.table> --}}
{{--     @endif --}}

{{--     @if(count($salary_detail_3) > 0) --}}
    {{-- 余白 --}}
{{--     <div class="mb-3"></div> --}}

{{--     <x-bs.form-title>その他</x-bs.form-title> --}}

{{--     <x-bs.table :hover=false :vHeader=true class="mb-4"> --}}
        {{-- テーブル行 --}}
{{--         @for ($i = 0; $i < count($salary_detail_3); $i++) <tr> --}}
{{--             <th>{{$salary_detail_3[$i]->item_name}}</th> --}}
{{--             <td class="t-price">{{number_format($salary_detail_3[$i]->amount)}}</td> --}}
{{--             </tr> --}}
{{--             @endfor --}}
{{--     </x-bs.table> --}}
{{--     @endif --}}

    {{-- 余白 --}}
{{--     <div class="mb-3"></div> --}}

{{--     <x-bs.form-title>合計</x-bs.form-title> --}}

{{--     <x-bs.table :hover=false :vHeader=true class="mb-4"> --}}
        {{-- テーブル行 --}}
{{--         @for ($i = 0; $i < count($salary_detail_4); $i++) <tr> --}}
{{--             <th>{{$salary_detail_4[$i]->item_name}}</th> --}}
{{--             <td class="t-price">{{number_format($salary_detail_4[$i]->amount)}}</td> --}}
{{--             </tr> --}}
{{--             @endfor --}}
{{--     </x-bs.table> --}}

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