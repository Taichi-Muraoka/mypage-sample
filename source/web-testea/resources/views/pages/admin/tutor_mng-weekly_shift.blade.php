@extends('adminlte::page')

@section('title', '講師空き時間')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォームなし --}}
<x-bs.card>
    <x-slot name="card_title">
        {{$tutor_name}}
    </x-slot>
    <x-bs.table :hover=false class="table-checked">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum t-week-time"></th>

            {{-- 曜日を表示 --}}
            @foreach ($weekdayList as $key => $obj)
            <th class="t-week">{{$obj->value}}</th>
            @endforeach
        </x-slot>

        {{-- 二重ループで組み立てる --}}
        @foreach ($periodList as $periodKey => $periodVal)
            {{-- 時限を表示 --}}
            <td class="tt">{{$periodKey}}限</td>

            @foreach ($weekdayList as $weekdayKey => $weekdayVal)
            <td>
                {{-- チェックボックス。裏でクリックされた時間帯を保持している --}}
                <x-input.checkbox id="{{$weekdayKey}}_{{$periodKey}}" class="chk-wt" name="chkWs" :icheck=false
                    value="{{$weekdayKey}}_{{$periodKey}}" :editData=$editData :exceptData=$exceptData />

                {{-- 表のDiv --}}
                <div class="chk-t" data-wt="{{$weekdayKey}}_{{$periodKey}}"></div>
            </td>
            @endforeach

            </tr>
            @endforeach

    </x-bs.table>

    <p><br>黒色：レギュラー授業&nbsp;／&nbsp;緑色：空き時間</p>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-start">
            <x-button.back />
        </div>
    </x-slot>

</x-bs.card>

@stop
