@extends('pages.common.modal')

@section('modal-body')

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">登録日</th>
        <td>@{{item.regist_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.sname}}</td>
    </tr>
    <tr>
        <th>試験種別</th>
        <td>@{{item.type_name}}</td>
    </tr>
    <tr>
        <th>試験名</th>
        <td>@{{item.teiki_name}}@{{item.moshi_name}}</td>
    </tr>
    <tr>
        <th colspan="2">試験成績</th>
    </tr>
    <tr>
        <td colspan="2">

            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false class="noborder-only-top">
                <x-slot name="thead">
                    <th width="25%">教科</th>
                    <th width="25%">得点</th>
                    <th width="25%">前回比</th>
                    <th width="25%">学年平均</th>
                </x-slot>

                <tr v-for="gradesDetail in item.gradesDetails" v-cloak>
                    <x-bs.td-sp>@{{gradesDetail.curriculum_name}}</x-bs.td-sp>
                    <x-bs.td-sp>@{{gradesDetail.score}}点</x-bs.td-sp>
                    <x-bs.td-sp>@{{gradesDetail.updown}}</x-bs.td-sp>
                    <x-bs.td-sp vShow="gradesDetail.average != null">@{{gradesDetail.average}}点</x-bs.td-sp>
                    <x-bs.td-sp vShow="gradesDetail.average == null"></x-bs.td-sp>
                </tr>

            </x-bs.table>
        </td>
    </tr>

    <tr>
        <th colspan="2">次回の試験に向けての抱負</th>
    </tr>
    {{-- nl2br: 改行 --}}
    <td colspan="2" class="nl2br">@{{item.student_comment}}</td>
</x-bs.table>

@overwrite